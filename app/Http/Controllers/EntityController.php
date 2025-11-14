<?php

namespace App\Http\Controllers;

use App\Constants\Actions;
use App\Helpers\ActionsLogHelper;
use App\Helpers\PermissionsHelper;
use App\Models\Channel;
use Illuminate\Database\Eloquent\Model;

class EntityController extends Controller {

    protected $entity_class;
    protected $permissions = [
        'approve' => '',
        'create' => '',
        'delete' => ''
    ];

    protected $redirect_after_delete = '/';

    public function save() {
        $entity = new $this->entity_class;
        if (!PermissionsHelper::allows($this->permissions['create'])) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        return $this->fillData($entity);
    }

    public function update($id) {
        $entity = $this->entity_class::find($id);
        if (!$entity) {
            return [
                'status' => 0,
                'text' => 'Материал не найден'
            ];
        }
        if (!$entity->can_edit) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        return $this->fillData($entity);
    }

    protected function fillData($entity)
    {
        $this->saveEntity($entity);
        return [
            'status' => 1,
            'text' => 'Информация обновлена',
        ];
    }

    protected function saveEntity($entity)
    {
        ActionsLogHelper::create($entity, $entity->id ? Actions::Update : Actions::Create);
        $entity->save();
    }

    public function approve() {
        $entity = $this->entity_class::find(request()->input('id'));
        if (!$entity) {
            return [
                'status' => 0,
                'text' => 'Материал не найден'
            ];
        }
        $can_approve = PermissionsHelper::allows($this->permissions['approve']);
        if ($can_approve) {

            $pending = request()->input('status', !$entity->pending);
            $entity->pending = $pending;

            ActionsLogHelper::create($entity, $pending ? Actions::Approve : Actions::Unapprove);

            $entity->save();

            return [
                'status' => 1,
                'data' => [
                    'approved' => !$pending
                ]
            ];
        } else {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
    }

    public function delete()
    {
        $entity = $this->entity_class::find(request()->input('id'));
        if (!$entity) {
            return [
                'status' => 0,
                'text' => 'Материал не найден'
            ];
        }
        $can_edit_global = isset($this->permissions['delete']) && PermissionsHelper::allows($this->permissions['delete']);
        if (!$entity->can_edit && !$can_edit_global) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }

        $entity->delete();
        return $this->afterDelete($entity);
    }

    protected function afterDelete(Model $entity)
    {
        return [
            'status' => 1,
            'text' => 'Удалено',
            'redirect_to' => $this->redirect_after_delete
        ];
    }

}
