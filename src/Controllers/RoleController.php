<?php

namespace OpenAdminCore\Admin\Controllers;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Foundation\Application;
use OpenAdminCore\Admin\Form;
use OpenAdminCore\Admin\Grid;
use OpenAdminCore\Admin\Show;

class RoleController extends AdminController
{
    /**
     * {@inheritdoc}
     */
    protected function title(): Application|array|string|Translator
    {
        return __('admin.roles');
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        $roleModel = config('admin.database.roles_model');

        $grid = new Grid(new $roleModel());

        $grid->column('id', 'ID')->sortable();
        $grid->column('slug', __('admin.slug'))->sortable();
        $grid->column('name', __('admin.name'))->sortable();

        $grid->column('permissions', __('admin.permission'))->pluck('name')->label();

        $grid->column('created_at', __('admin.created_at'))->sortable();
        $grid->column('updated_at', __('admin.updated_at'))->sortable();

        $grid->actions(function (Grid\Displayers\Actions\Actions $actions) {
            if ($actions->row->slug == 'administrator') {
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
        $roleModel = config('admin.database.roles_model');

        $show = new Show($roleModel::findOrFail($id));

        $show->field('id', 'ID');
        $show->field('slug', __('admin.slug'));
        $show->field('name', __('admin.name'));
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
        $permissionModel = config('admin.database.permissions_model');
        $roleModel = config('admin.database.roles_model');

        $form = new Form(new $roleModel());

        $form->display('id', 'ID');

        $form->text('slug', __('admin.slug'))->rules('required');
        $form->text('name', __('admin.name'))->rules('required');
        $form->listbox('permissions', __('admin.permissions'))->options($permissionModel::all()->pluck('name', 'id'))->height(300);

        $form->display('created_at', __('admin.created_at'));
        $form->display('updated_at', __('admin.updated_at'));

        // Защита роли администратора
        $form->editing(function (Form $form) {
            if ($form->model()->slug == 'administrator') {
                $form->disableEditingCheck();
                $form->tools(function (Form\Tools $tools) {
                    $tools->disableDelete();
                    $tools->disableList();
                });
                // Делаем поля только для чтения
                //$form->disableEditing();
            }
        });

        $form->deleting(function (Form $form) {
            return $form->model()->slug != 'administrator';
        });


        return $form;
    }
}
