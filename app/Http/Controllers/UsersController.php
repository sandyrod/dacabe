<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\UserEditRequest;
use App\User;
use App\Permission;
use App\{Role};
use App\Models\{Company, Vendedor};
use Illuminate\Validation\ValidationException;
use App\Traits\UserTrait;

use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Response;

class UsersController extends Controller
{
    use UserTrait;

    private $permission;
    private $module;
    private $validate_create;
    private $validate_edit;

    public function __construct()
    {
        $this->middleware('auth');
        $this->permission = 'user';
        $this->module = 'users';
        $this->validate_create = 'UserCreate';
        $this->validate_edit = 'UserEdit';
    }

    public function index(Request $request)
    {
        if ( ! hasPermission($this->permission) )
            abort(403);
     
        $user = new User();
    	$data = $user->getData();
     
        return (requestAjaxOrJson($request, $data)) ? $this->getJsonOrDatatableResponse($request, $data) : view($this->module.'.index');
    }

    public function impersonate(Request $request)
    {
        $admin = Auth::user();
        if (!$admin || !$admin->canImpersonateSellers() || session()->has('impersonator')) {
            abort(403);
        }

        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = strtolower(trim($validated['email']));
        $seller = User::whereRaw('LOWER(TRIM(email)) = ?', [$email])->first();
        $sellerExists = (new Vendedor)
            ->whereRaw('LOWER(TRIM(email)) = ?', [$email])
            ->exists();
        $sellerRole = Role::where('name', 'vendedor')->first();

        if (!$seller || $seller->id === $admin->id || !$sellerExists || !$sellerRole) {
            throw ValidationException::withMessages([
                'impersonation' => 'No se encontró un usuario vendedor con ese correo. Verifica la dirección e inténtalo nuevamente.',
            ]);
        }

        session([
            'impersonator' => [
                'user_id' => $admin->id,
                'had_active_role' => session()->has('active_role_id'),
                'active_role_id' => session('active_role_id'),
            ],
        ]);

        Auth::login($seller);
        session([
            'active_role_id' => $sellerRole->id,
            'active_role_name' => $sellerRole->display_name,
        ]);
        $request->session()->regenerate();

        return redirect()->route('inicio')
            ->with('info', 'Ahora estás usando la cuenta de ' . $seller->email . '.');
    }

    public function stopImpersonating(Request $request)
    {
        $impersonator = session('impersonator');
        if (!$impersonator) {
            abort(403);
        }

        $admin = User::find($impersonator['user_id']);
        if (!$admin || !$admin->canImpersonateSellers()) {
            abort(403);
        }

        Auth::login($admin);
        session()->forget(['impersonator', 'active_role_id', 'active_role_name']);

        $ordersAdminRole = $admin->roles()->where('name', 'admin_pedidos')->first();
        $previousRole = $impersonator['had_active_role']
            ? $admin->roles()->where('role_id', $impersonator['active_role_id'])->first()
            : null;
        $activeRole = $ordersAdminRole ?: $previousRole ?: $admin->roles()->first();
        if ($activeRole) {
            session([
                'active_role_id' => $activeRole->id,
                'active_role_name' => $activeRole->display_name,
            ]);
        }

        $request->session()->regenerate();

        return redirect()->route('manager.dashboard')->with('info', 'Has vuelto a tu cuenta de administrador.');
    }

    public function store(UserCreateRequest $request)
    {
        if ( ! hasPermission('create-'.$this->permission) )
            abort(403);

        $user = User::create($request->all());
       
        return redirect()->route($this->module.'.index')
                ->with('info', 'El registro '.$user->name.' ha sido creado satisfactoriamente');
    }

    public function create()
    {
        if ( ! hasPermission('create-'.$this->permission) )
            abort(403);

        $companies = (new Company)->orderBy('name')->get();
        $route = $this->module.'.index';
        $validate = $this->validate_create;
        return view($this->module.'.create', compact(['route', 'validate', 'companies']));
    }

    public function update(UserEditRequest $request, User $user)
    {
        if ( ! hasPermission('edit-'.$this->permission) && ! $this->isMySession(Auth::user()->id) )
            abort(403);

        $prepared_request = ($request->password) ? $request->except('file') : $request->except('password', 'file');
        $user->fill($prepared_request)->save();
       
        if ($request->file('file'))
            $this->savePhoto($request, $user);

        return $this->redirectUserUpdate($user);
    }

    private function savePhoto($request, User $user)
    {
        $file_name = $this->uploadPhoto($request);
        $user->fill(['photo'=>$file_name])->save();
    }

    private function uploadPhoto($request)
    {
        /*
        $name = $request->file('file')->getClientOriginalName();
        $ext = substr($name, strlen($name)-4);
        $ext = substr($ext, 0, 1) == '.' ? $ext : '.' . $ext;
        $ext = $ext == '.jpg' ? '.jpeg' : $ext;
        $path = Storage::disk('public')->put('users', $request->file('file'));
    
        return pathinfo($path)['filename'].$ext;
        */
    
        $name = Storage::disk('local')->put('public/users', $request->file('file'));
        return substr($name, 13); 
    }

    public function edit(User $user)
    {
        if ( ! hasPermission('edit-'.$this->permission) )
            abort(403);

        $companies = (new Company)->orderBy('name')->get();
        $route = $this->module.'.index';
        $validate = $this->validate_edit;
        return view($this->module.'.edit', compact(['user', 'route', 'validate', 'companies']));
    }

    public function destroy(User $user)
    {
        if ( ! hasPermission('delete-'.$this->permission) )
            abort(403);

        $name = $user->name;
        $user->delete();

        return Response::json([
            'type' => 'success',
            'title' => 'Registro Eliminado!',
            'text' => 'El registro '.$name . ' ha sido eliminado satisfactoriamente.'
        ], 200);
    }


    public function show(User $user)
    {
        if ( ! $this->isMySession($user->id))
            abort(403);

        $companies = null;  
        $route = 'inicio';
        $validate = $this->validate_edit;
        $profile = 1;
        return view($this->module.'.show', compact(['user', 'route', 'validate', 'profile', 'companies']));
    }

    public function showCardList()
    {
        $user = new User();
        $users = $user->getPaginate(6);

        return view($this->module.'.cards', compact(['users']));
    }

    public function showPrintList()
    {
        if ( ! hasPermission($this->permission) )
            abort(403);

        $user = new User();
        $users = $user->getData();
        $print = 1;
        
        $report_data = $user->getReportConfig();

        return view($this->module.'.partials.print', compact(['users', 'print', 'report_data']));
    }

    public function updatePermissions(Request $request)
    {
        if ( ! hasPermission('edit-'.$this->permission) )
            abort(403);

        $user = User::findOrFail($request->user_id);
        $permissions = ($request->permissions) ? $request->permissions : [];
        $user->syncPermissions($permissions);
        $roles = ($request->roles) ? $request->roles : [];
        $user->syncRoles($roles);
        


        return redirect()->route('users.index')
                ->with('info', 'Los permisos de '.$user->name.' han sido actualizados satisfactoriamente');
    }

    public function editPermissions($user_id)
    {
        if ( ! hasPermission('edit-'.$this->permission) )
            abort(403);

        $user = User::findOrFail($user_id);

        $route = $this->module.'.index';
        $validate = $this->validate_edit;

        $permission = new Permission();
        $permissions = $permission->getUserPermissions($user->id);

        $role = new Role();
        $roles = $role->getUserRoles($user->id);

        return view($this->module.'.edit_permissions', compact(['user', 'route', 'validate', 'permissions', 'roles']));
    }

}
