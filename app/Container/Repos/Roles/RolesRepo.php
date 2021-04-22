<?php
namespace Repos\Roles;

use Contracts\Roles\RolesContract;
use App\Role;
use App\Permission;

class RolesRepo implements RolesContract
{

    private $pagination = 15;

    public function __construct(Role $role)
    {
        $this->role = $role;
    }

    public function get($id)
    {
        return $this->role->findOrFail($id);
    }

    public function getAll()
    {
        return $this->role->all();
    }

    public function backendFilter($request)
    {
        $q = $this->role;

        if (isset($request->keyword) && !empty($request->keyword))
            $q = $q->where('name', 'LIKE', '%'.$request->keyword.'%');

        return (object) [
            'count' => $q->count(),
            'data'  => $q->paginate($this->pagination)
        ];
    }

    public function getPaginated()
    {
        return $this->role->paginate($this->pagination);
    }

    public function permissions($role)
    {
        return $role->belongsToMany(Permission::class);
    }

    public function permissionsIds($id)
    {
        $role = $this->get($id);

        return $this->permissions($role)->allRelatedIds()->toArray();
    }

    public function set($data)
    {
        $this->role->label = $data->label;
        $this->role->name  = make_slug($data->label);

        $this->role->save();

        if(!empty($data->permissions))
            $this->role->permissions()->sync($data->permissions);

        return true;
    }

    public function update($data, $id)
    {
        $role = $this->get($id);

        $role->label = $data->label;
        $role->name  = make_slug($data->label);

        $role->save();

        if(!empty($data->permissions))
            $role->permissions()->sync($data->permissions);

        return true;
    }

    public function delete($id)
    {
        return $this->get($id)->delete();
    }
}
