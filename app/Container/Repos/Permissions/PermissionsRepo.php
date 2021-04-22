<?php
namespace Repos\Permissions;

use Contracts\Permissions\PermissionsContract;
use App\Permission;
use DB;

class PermissionsRepo implements PermissionsContract
{
    public function __construct(Permission $permission)
    {
        $this->permission = $permission;
    }

    public function get($id)
    {
        $this->permission->findOrFail($id);
    }

    public function getAll()
    {
        return $this->permission->all();
    }

    public function getAllGrouped()
    {
        return $this->permission->select(DB::raw('GROUP_CONCAT(id) as ids, GROUP_CONCAT(name) as name, label, GROUP_CONCAT(type) as type'))
          ->groupBy('order')->groupBy('label')->orderBy('order')->get();
    }

    public function getAllArranged()
    {
        $permissions       = [];
        $groupedPermission = $this->getAllGrouped();
        foreach ($groupedPermission as $key => $permission) {
            $name  = explode(',', $permission->name);
            $id    = explode(',', $permission->ids);
            $type  = explode(',', $permission->type);

            $permissions[$key]['label'] = $permission->label;
            for ($i=0; $i < 4; $i++) {
                $permissions[$key]['permissions'][$i]['name'] = null;
                $permissions[$key]['permissions'][$i]['id']   = null;
            }

            foreach ($type as $typeKey => $value) {
                if ($value == 'view') {
                    $permissions[$key]['permissions'][0]['name'] = $name[$typeKey];
                    $permissions[$key]['permissions'][0]['id']   = $id[$typeKey];
                } else if ($value == 'add') {
                    $permissions[$key]['permissions'][1]['name'] = $name[$typeKey];
                    $permissions[$key]['permissions'][1]['id']   = $id[$typeKey];
                } else if ($value == 'edit') {
                    $permissions[$key]['permissions'][2]['name'] = $name[$typeKey];
                    $permissions[$key]['permissions'][2]['id']   = $id[$typeKey];
                } else if ($value == 'delete') {
                    $permissions[$key]['permissions'][3]['name'] = $name[$typeKey];
                    $permissions[$key]['permissions'][3]['id']   = $id[$typeKey];
                }
            }
        }

        return $permissions;
    }
    public function set($data)
    {
        return true;
    }

    public function delete()
    {
        return $this->permission->delete();
    }
}
