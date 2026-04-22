<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laratrust\Traits\LaratrustUserTrait;

use Laravel\Passport\HasApiTokens;

use App\Models\Notification;
use App\Models\Company;
use App\Role;
use App\Models\Vendedor;

use Auth;
use DB;

class User extends Authenticatable
{
    use LaratrustUserTrait {
        hasRole as hasRoleLaratrust;
        isAbleTo as isAbleToLaratrust;
    }
    use Notifiable;
    use HasApiTokens;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'sdcloud.users';
    
    protected $fillable = [
        'name', 'email', 'password', 'last_name', 'document', 'phone', 'mobile', 'birthday', 'photo', 'department_id', 'company_id', 'dashboard'
    ];


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function vendedor()
    {
        $vendedor = Vendedor::on('company')
            ->where('email', $this->email)
            ->first();
        
        return $vendedor;
    }

    public function pagos() {
        return $this->hasMany(\App\Models\Pago::class, 'user_id');
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($user) {
            if (! $user->company_id) {
                $user->company_id = (Auth::user())?Auth::user()->company_id:1;
            }
        });

        static::updated(function ($user) {
            Notification::create([
                'description' => 'Usuario Actualizado',
                'icon' => 'fa fa-user',
                'level' => 1,
                'user_id' => Auth::user()->id,
            ]);
        });
    }

    public function setPasswordAttribute($value){
        $this->attributes['password'] = bcrypt($value);
    }

    public function getFullNameAttribute() {
        return "{$this->name} {$this->last_name}";
    }

    public function getData(){
        return $this->get();
    }

    public function getPaginate(){
        return $this->with('roles')->paginate(6);
    }
    
    public function migrateSugau(){
        $users_sugau = DB::connection('pgsql')
                ->table('amusuari')
                ->select('uscedula', 'usclave')
                ->get();

        foreach ($users_sugau as $sugau) 
            $this->updateUsersFromSugau($sugau);
    }

    public function updateUsersFromSugau($sugau){
        $user = $this->where('document', $sugau->uscedula)->first();
        if (! $user ){
            $user = DB::connection('pgsql')
                ->table('amperson')
                ->select('nombre', 'apellido')
                ->where('cedula', $sugau->uscedula)
                ->first();

            if ($user) 
                $this->create([
                    'name' => $user->nombre,
                    'last_name' => $user->apellido,
                    'document' => $sugau->uscedula,
                    'email' => '',
                    'password' => $sugau->usclave,
                    'company_id' => 1,
                    'status' => 1,
                ]);
        }
    }

    public function getReportConfig(){
        return [
            'title' => 'Listado de Usuarios',
            'company' => Auth::user()->company
        ];
    }

    public function isAdministrator()
    {
       return $this->hasRole('admin');
    } 

    public function isAdministrativerUser()
    {
       return $this->hasRole('admin_user');
    }

    public function getActiveRole()
    {
        if (session()->has('active_role_id')) {
            return \App\Role::find(session('active_role_id'));
        }
        return $this->roles()->first();
    }

    public function hasRole($name, $team = null, $requireAll = false)
    {
        if (session()->has('active_role_id')) {
            $activeRole = \App\Role::find(session('active_role_id'));
            if ($activeRole) {
                if (is_array($name)) {
                    foreach ($name as $roleName) {
                        if ($activeRole->name == $roleName) {
                            return true;
                        }
                    }
                    return false;
                }
                return $activeRole->name == $name;
            }
        }
        return $this->realHasRole($name, $team, $requireAll);
    }

    /**
     * Rename the original Laratrust hasRole to avoid recursion
     */
    public function realHasRole($name, $team = null, $requireAll = false)
    {
        return $this->hasRoleLaratrust($name, $team, $requireAll);
    }

    /**
     * LaratrustUserTrait uses hasRole, so we need to be careful.
     * Actually LaratrustUserTrait provides hasRole. 
     * We'll use the trait's method by alias if possible, but PHP traits don't easily allow that without modification.
     * Alternative: Use the trait's check directly or check session first.
     */

    public function isAbleTo($permission, $team = null, $requireAll = false)
    {
        if (session()->has('active_role_id')) {
            $activeRole = \App\Role::find(session('active_role_id'));
            if ($activeRole) {
                return $activeRole->hasPermission($permission);
            }
        }
        return $this->isAbleToLaratrust($permission, $team, $requireAll);
    }
}
