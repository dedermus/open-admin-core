<?php

namespace OpenAdminCore\Admin\Controllers;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Hash;
use OpenAdminCore\Admin\Form;
use OpenAdminCore\Admin\Grid;
use OpenAdminCore\Admin\Show;

class UserController extends AdminController
{
    /**
     * {@inheritdoc}
     */
    protected function title(): Application|array|string|Translator|null
    {
        return __('admin.administrator');
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        $userModel = config('admin.database.users_model');

        $grid = new Grid(new $userModel());

        $grid->column('id', 'ID')->sortable();
        $grid->column('username', __('admin.username'))->sortable();
        $grid->column('name', __('admin.name'))->sortable();
        $grid->column('email', __('admin.email'))->sortable();
        $grid->column('roles', __('admin.roles'))->pluck('name')->label();
        $grid->column('created_at', __('admin.created_at'))->sortable();
        $grid->column('updated_at', __('admin.updated_at'))->sortable();

        $grid->actions(function (Grid\Displayers\Actions\Actions $actions) {
            // Проверяем, есть ли у пользователя роль "administrator"
            $hasAdminRole = $actions->row->roles->contains('slug', 'administrator');
            if ($hasAdminRole) {
                $actions->disableDelete();
            }
        });

        $grid->tools(function (Grid\Tools $tools) {
            $tools->batch(function (Grid\Tools\BatchActions $actions) {
                $actions->disableDelete();
            });
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail(mixed $id): Show
    {
        $userModel = config('admin.database.users_model');

        $show = new Show($userModel::findOrFail($id));

        $show->field('id', 'ID');
        $show->field('username', __('admin.username'));
        $show->field('name', __('admin.name'));
        $show->field('roles', __('admin.roles'))->as(function ($roles) {
            return $roles->pluck('name');
        })->label();
        $show->field('permissions', __('admin.permissions'))->as(function ($permission) {
            return $permission->pluck('name');
        })->label();
        $show->field('created_at', __('admin.created_at'));
        $show->field('updated_at', __('admin.updated_at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    public function form(): Form
    {
        $userModel = config('admin.database.users_model');
        $permissionModel = config('admin.database.permissions_model');
        $roleModel = config('admin.database.roles_model');

        $form = new Form(new $userModel());

        $userTable = config('admin.database.users_table');
        $connection = config('admin.database.connection');

        $form->display('id', 'ID');
        $form->text('username', __('admin.username'))
            ->creationRules(['required', "unique:{$connection}.{$userTable}"])
            ->updateRules(['required', "unique:{$connection}.{$userTable},username,{{id}}"]);

        $form->text('name', __('admin.name'))->rules('required');
        $form->image('avatar', __('admin.avatar'));
        $form->password('password', __('admin.password'))->rules('required|confirmed');
        $form->password('password_confirmation', __('admin.password_confirmation'))->rules('required')
            ->default(function ($form) {
                return $form->model()->password;
            });

        $form->ignore(['password_confirmation']);

        $form->multipleSelect('roles', __('admin.roles'))->options($roleModel::all()->pluck('name', 'id'));
        $form->multipleSelect('permissions', __('admin.permissions'))->options($permissionModel::all()->pluck('name', 'id'));

        $form->display('created_at', __('admin.created_at'));
        $form->display('updated_at', __('admin.updated_at'));

        $form->saving(function (Form $form) {
            if ($form->password && $form->model()->password != $form->password) {
                $form->password = Hash::make($form->password);
            }
        });

        return $form;
    }
}
