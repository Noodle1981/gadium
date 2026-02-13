<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class UserTable extends Component
{
    use WithPagination;

    public $search = '';
    public $roleFilter = '';
    public $showRevoked = false;
    public $perPage = 10;

    protected $queryString = ['search', 'roleFilter'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRoleFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        $users = User::query()
            ->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'Super Admin');
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->roleFilter, function ($query) {
                $query->whereHas('roles', function ($q) {
                    $q->where('name', $this->roleFilter);
                });
            })
            ->when($this->showRevoked, function ($query) {
                $query->onlyTrashed();
            })
            ->with('roles')
            ->latest()
            ->paginate($this->perPage);

        $roles = \Spatie\Permission\Models\Role::where('name', '!=', 'Super Admin')->get();

        return view('livewire.user-table', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }
    public function restore($id)
    {
        $user = User::withTrashed()->find($id);
        
        if ($user) {
            $user->restore();
            session()->flash('success', 'Acceso restaurado exitosamente para el usuario ' . $user->name);
        }
    }

    public function forceDelete($id)
    {
        $user = User::withTrashed()->find($id);

        if ($user) {
            $user->forceDelete();
            session()->flash('success', 'Usuario eliminado definitivamente: ' . $user->name);
        }
    }
}
