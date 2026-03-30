<?php

namespace OpenAdminCore\Admin\Grid\Tools;

use Illuminate\Support\Facades\Request;
use OpenAdminCore\Admin\Actions\BatchAction;
use OpenAdminCore\Admin\Actions\Response;

class BatchDelete extends BatchAction
{
    public $icon = 'icon-trash';

    public function __construct()
    {
        $this->name = trans('admin.batch_delete');
    }

    /**
     * Script of batch delete action.
     */
    public function script()
    {
        return <<<JS
        document.querySelector('{$this->getSelector()}').addEventListener("click",function(){
            let resource_url = '{$this->resource}/' + admin.grid.selected.join();
            admin.resource.batch_delete(resource_url);
        });
JS;
    }

    /**
     * Handle the batch delete action.
     *
     */
    public function handle()
    {

    }
}
