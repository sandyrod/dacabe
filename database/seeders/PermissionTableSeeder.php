<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Permission;
use App\Role;

class PermissionTableSeeder extends Seeder
{
    private function getPermissions()
    {
        $permission = [];

        $permission[] = ['role', 'Roles', 'Roles', 'roles', 'rol roles Seguridad Usuario Permiso Perfil configura'];
        $permission[] = ['create-role', 'Crear Rol', 'Roles', '', ''];
        $permission[] = ['edit-role', 'Editar Rol', 'Roles', '', ''];
        $permission[] = ['delete-role', 'Eliminar Rol', 'Roles', '', ''];

        $eng = 'user';
        $esp = 'Usuario';
        $permission[] = [$eng, $esp.'s', $esp.'s', $eng.'s', 'Seguridad Usuario usuarios Permiso Perfil configura'];
        $permission[] = ['create-'.$eng, 'Crear '.$esp, $esp.'s', '', ''];
        $permission[] = ['edit-'.$eng, 'Editar '.$esp, $esp.'s', '', ''];
        $permission[] = ['delete-'.$eng, 'Eliminar '.$esp, $esp.'s', '', ''];

        $eng = 'permission';
        $esp = 'Permiso';
        $permission[] = [$eng, $esp.'s', $esp.'s', $eng.'s', 'Seguridad Usuario Permiso permisos Perfil configura Opcion'];
        $permission[] = ['create-'.$eng, 'Crear '.$esp, $esp.'s', '', ''];
        $permission[] = ['edit-'.$eng, 'Editar '.$esp, $esp.'s', '', ''];
        $permission[] = ['delete-'.$eng, 'Eliminar '.$esp, $esp.'s', '', ''];

        $eng = 'company';
        $esp = 'Empresa';
        $permission[] = [$eng, $esp.'s', $esp.'s', 'companies', 'Empresa empresas configura'];
        $permission[] = ['create-'.$eng, 'Crear '.$esp, $esp.'s', '', ''];
        $permission[] = ['edit-'.$eng, 'Editar '.$esp, $esp.'s', '', ''];
        $permission[] = ['delete-'.$eng, 'Eliminar '.$esp, $esp.'s', '', ''];

        $eng = 'company-status';
        $esp = 'Estatus Empresa';
        $permission[] = [$eng, $esp.'s', $esp.'s', 'companies', 'Empresa empresas configura'];
        $permission[] = ['create-'.$eng, 'Crear '.$esp, $esp.'s', '', ''];
        $permission[] = ['edit-'.$eng, 'Editar '.$esp, $esp.'s', '', ''];
        $permission[] = ['delete-'.$eng, 'Eliminar '.$esp, $esp.'s', '', ''];

        $eng = 'department';
        $esp = 'Departamento';
        $permission[] = [$eng, $esp.'s', $esp.'s', $eng.'s', 'departamento departamentos oficina division'];
        $permission[] = ['create-'.$eng, 'Crear '.$esp, $esp.'s', '', ''];
        $permission[] = ['edit-'.$eng, 'Editar '.$esp, $esp.'s', '', ''];
        $permission[] = ['delete-'.$eng, 'Eliminar '.$esp, $esp.'s', '', ''];

        $eng = 'page';
        $esp = 'Pagina';
        $permission[] = [$eng, $esp.'s', $esp.'s', $eng.'s', 'pagina paginas sitio web landing'];
        $permission[] = ['create-'.$eng, 'Crear '.$esp, $esp.'s', '', ''];
        $permission[] = ['edit-'.$eng, 'Editar '.$esp, $esp.'s', '', ''];
        $permission[] = ['delete-'.$eng, 'Eliminar '.$esp, $esp.'s', '', ''];

        $eng = 'notice';
        $esp = 'Noticia';
        $permission[] = [$eng, $esp.'s', $esp.'s', $eng.'s', 'pagina paginas sitio web landing'];
        $permission[] = ['create-'.$eng, 'Crear '.$esp, $esp.'s', '', ''];
        $permission[] = ['edit-'.$eng, 'Editar '.$esp, $esp.'s', '', ''];
        $permission[] = ['delete-'.$eng, 'Eliminar '.$esp, $esp.'s', '', ''];

        $eng = 'category';
        $esp = 'Categoria';
        $permission[] = [$eng, $esp.'s', $esp.'s', 'categories', 'noticia blog informacion web sitio categoria categorias'];
        $permission[] = ['create-'.$eng, 'Crear '.$esp, $esp.'s', '', ''];
        $permission[] = ['edit-'.$eng, 'Editar '.$esp, $esp.'s', '', ''];
        $permission[] = ['delete-'.$eng, 'Eliminar '.$esp, $esp.'s', '', ''];

        $eng = 'module';
        $esp = 'Modulo';
        $permission[] = [$eng, $esp.'s', $esp.'s', $eng.'s', 'modulo seguridad opciones perfiles'];
        $permission[] = ['create-'.$eng, 'Crear '.$esp, $esp.'s', '', ''];
        $permission[] = ['edit-'.$eng, 'Editar '.$esp, $esp.'s', '', ''];
        $permission[] = ['delete-'.$eng, 'Eliminar '.$esp, $esp.'s', '', ''];

        $eng = 'company-status';
        $esp = 'Estatus de Empresa';
        $permission[] = [$eng, $esp.'s', $esp.'s', $eng, 'Estatus Empresa seguridad opciones configuracion'];
        $permission[] = ['create-'.$eng, 'Crear '.$esp, $esp, '', ''];
        $permission[] = ['edit-'.$eng, 'Editar '.$esp, $esp, '', ''];
        $permission[] = ['delete-'.$eng, 'Eliminar '.$esp, $esp, '', ''];

        $eng = 'serial';
        $esp = 'Serial SoftDesign';
        $permission[] = [$eng, $esp.'s', $esp, $eng, 'Estatus Empresa seriales acceso softdesign clave personaliza'];
        $permission[] = ['create-'.$eng, 'Crear '.$esp, $esp, '', ''];
        $permission[] = ['edit-'.$eng, 'Editar '.$esp, $esp, '', ''];
        $permission[] = ['delete-'.$eng, 'Eliminar '.$esp, $esp, '', ''];

        $eng = 'dollar';
        $esp = 'Tasa Divisa';
        $permission[] = [$eng, $esp.'s', $esp.'s', $eng, 'Dolar Tasa Divisa Precio Producto'];
        $permission[] = ['create-'.$eng, 'Crear '.$esp, $esp.'s', '', ''];
        $permission[] = ['edit-'.$eng, 'Editar '.$esp, $esp.'s', '', ''];
        $permission[] = ['delete-'.$eng, 'Eliminar '.$esp, $esp.'s', '', ''];

        $eng = 'command';
        $esp = 'Comando';
        $permission[] = [$eng, $esp.'s', $esp.'s', $eng, 'comando automatizar softdesign administrativo herramienta'];
        $permission[] = ['create-'.$eng, 'Crear '.$esp, $esp.'s', '', ''];
        $permission[] = ['edit-'.$eng, 'Editar '.$esp, $esp.'s', '', ''];
        $permission[] = ['delete-'.$eng, 'Eliminar '.$esp, $esp.'s', '', ''];
        
        $eng = 'ftp-invoice';
        $esp = 'Factura de Compra';
        $permission[] = [$eng, $esp.'s', $esp.'s', $eng, 'factura compra ftp drogueria'];
        $permission[] = ['create-'.$eng, 'Crear '.$esp, $esp.'s', '', ''];
        $permission[] = ['edit-'.$eng, 'Editar '.$esp, $esp.'s', '', ''];
        $permission[] = ['delete-'.$eng, 'Eliminar '.$esp, $esp.'s', '', ''];

        $eng = 'inven';
        $esp = 'Inventario ControlCloud';
        $permission[] = [$eng, $esp, $esp, $eng, 'inventario softdesign'];
        $permission[] = ['create-'.$eng, 'Crear '.$esp, $esp, '', ''];
        $permission[] = ['edit-'.$eng, 'Editar '.$esp, $esp, '', ''];
        $permission[] = ['delete-'.$eng, 'Eliminar '.$esp, $esp, '', ''];

        $eng = 'notifications';
        $esp = 'notifications';
        $permission[] = [$eng, $esp, $esp, $eng, 'notificaciones softdesign'];
        $permission[] = ['create-'.$eng, 'Crear '.$esp, $esp, '', ''];
        $permission[] = ['edit-'.$eng, 'Editar '.$esp, $esp, '', ''];
        $permission[] = ['delete-'.$eng, 'Eliminar '.$esp, $esp, '', ''];

        $eng = 'drugstores';
        $esp = 'drugstores';
        $permission[] = [$eng, $esp, $esp, $eng, 'Droguerias softdesign'];
        $permission[] = ['create-'.$eng, 'Crear '.$esp, $esp, '', ''];
        $permission[] = ['edit-'.$eng, 'Editar '.$esp, $esp, '', ''];
        $permission[] = ['delete-'.$eng, 'Eliminar '.$esp, $esp, '', ''];

        $eng = 'ftp';
        $esp = 'ftp';
        $permission[] = [$eng, $esp, $esp, $eng, 'DrogueriaFtp s softdesign'];
        $permission[] = ['create-'.$eng, 'Crear '.$esp, $esp, '', ''];
        $permission[] = ['edit-'.$eng, 'Editar '.$esp, $esp, '', ''];
        $permission[] = ['delete-'.$eng, 'Eliminar '.$esp, $esp, '', ''];

        $eng = 'moviventas';
        $esp = 'moviventas';
        $permission[] = [$eng, $esp, $esp, $eng, 'Reportes gerenciales softdesign'];
        $permission[] = ['create-'.$eng, 'Crear '.$esp, $esp, '', ''];
        $permission[] = ['edit-'.$eng, 'Editar '.$esp, $esp, '', ''];
        $permission[] = ['delete-'.$eng, 'Eliminar '.$esp, $esp, '', ''];

        $eng = 'themes';
        $esp = 'tema';
        $permission[] = [$eng, $esp, $esp, $eng, 'Menu Ventas Landing temas web'];
        $permission[] = ['create-'.$eng, 'Crear '.$esp, $esp, '', ''];
        $permission[] = ['edit-'.$eng, 'Editar '.$esp, $esp, '', ''];
        $permission[] = ['delete-'.$eng, 'Eliminar '.$esp, $esp, '', ''];

        $eng = 'landings';
        $esp = 'landing';
        $permission[] = [$eng, $esp, $esp, $eng, 'Menu Ventas Landing web'];
        $permission[] = ['create-'.$eng, 'Crear '.$esp, $esp, '', ''];
        $permission[] = ['edit-'.$eng, 'Editar '.$esp, $esp, '', ''];
        $permission[] = ['delete-'.$eng, 'Eliminar '.$esp, $esp, '', ''];

        $eng = 'landing-settings';
        $esp = 'Configuracion de Landing';
        $permission[] = [$eng, $esp, $esp, $eng, 'Menu Ventas Configuracion Landing web'];
        $permission[] = ['create-'.$eng, 'Crear '.$esp, $esp, '', ''];
        $permission[] = ['edit-'.$eng, 'Editar '.$esp, $esp, '', ''];
        $permission[] = ['delete-'.$eng, 'Eliminar '.$esp, $esp, '', ''];

        $eng = 'pedidos';
        $esp = 'Pedidos';
        $permission[] = [$eng, $esp, $esp, $eng, 'Menu Ventas pedidos'];
        $permission[] = ['create-'.$eng, 'Crear '.$esp, $esp, '', ''];
        $permission[] = ['edit-'.$eng, 'Editar '.$esp, $esp, '', ''];
        $permission[] = ['delete-'.$eng, 'Eliminar '.$esp, $esp, '', ''];

        $eng = 'pedidos-inven';
        $esp = 'Productos';
        $permission[] = [$eng, $esp, $esp, $eng, 'Menu Ventas Productos'];
        $permission[] = ['create-'.$eng, 'Crear '.$esp, $esp, '', ''];
        $permission[] = ['edit-'.$eng, 'Editar '.$esp, $esp, '', ''];
        $permission[] = ['delete-'.$eng, 'Eliminar '.$esp, $esp, '', ''];

        $eng = 'grupos';
        $esp = 'Grupos';
        $permission[] = [$eng, $esp, $esp, $eng, 'Menu Ventas grupos'];
        $permission[] = ['create-'.$eng, 'Crear '.$esp, $esp, '', ''];
        $permission[] = ['edit-'.$eng, 'Editar '.$esp, $esp, '', ''];
        $permission[] = ['delete-'.$eng, 'Eliminar '.$esp, $esp, '', ''];

        $eng = 'marcas';
        $esp = 'Marcas';
        $permission[] = [$eng, $esp, $esp, $eng, 'Menu Ventas marcas'];
        $permission[] = ['create-'.$eng, 'Crear '.$esp, $esp, '', ''];
        $permission[] = ['edit-'.$eng, 'Editar '.$esp, $esp, '', ''];
        $permission[] = ['delete-'.$eng, 'Eliminar '.$esp, $esp, '', ''];

        $eng = 'vendedores';
        $esp = 'Vendedores';
        $permission[] = [$eng, $esp, $esp, $eng, 'Menu Ventas Vendedores'];
        $permission[] = ['create-'.$eng, 'Crear '.$esp, $esp, '', ''];
        $permission[] = ['edit-'.$eng, 'Editar '.$esp, $esp, '', ''];
        $permission[] = ['delete-'.$eng, 'Eliminar '.$esp, $esp, '', ''];

        $eng = 'comisiones';
        $esp = 'Comisiones';
        $permission[] = [$eng, $esp, $esp, $eng, 'Menu Ventas Comisiones'];
        $permission[] = ['create-'.$eng, 'Crear '.$esp, $esp, '', ''];
        $permission[] = ['edit-'.$eng, 'Editar '.$esp, $esp, '', ''];
        $permission[] = ['delete-'.$eng, 'Eliminar '.$esp, $esp, '', ''];

        $eng = 'clientes';
        $esp = 'Clientes';
        $permission[] = [$eng, $esp, $esp, $eng, 'Menu Ventas Clientes'];
        $permission[] = ['create-'.$eng, 'Crear '.$esp, $esp, '', ''];
        $permission[] = ['edit-'.$eng, 'Editar '.$esp, $esp, '', ''];
        $permission[] = ['delete-'.$eng, 'Eliminar '.$esp, $esp, '', ''];

        $eng = 'expense-groups';
        $esp = 'Grupos de Gastos';
        $permission[] = [$eng, $esp, $esp, $eng, 'Menu Gastos'];
        $permission[] = ['create-'.$eng, 'Crear '.$esp, $esp, '', ''];
        $permission[] = ['edit-'.$eng, 'Editar '.$esp, $esp, '', ''];
        $permission[] = ['delete-'.$eng, 'Eliminar '.$esp, $esp, '', ''];

        $eng = 'expenses';
        $esp = 'Gastos';
        $permission[] = [$eng, $esp, $esp, $eng, 'Menu Gastos'];
        $permission[] = ['create-'.$eng, 'Crear '.$esp, $esp, '', ''];
        $permission[] = ['edit-'.$eng, 'Editar '.$esp, $esp, '', ''];
        $permission[] = ['delete-'.$eng, 'Eliminar '.$esp, $esp, '', ''];

        $eng = 'branches';
        $esp = 'Sucursales';
        $permission[] = [$eng, $esp, $esp, $eng, 'Menu Sucursales'];
        $permission[] = ['create-'.$eng, 'Crear '.$esp, $esp, '', ''];
        $permission[] = ['edit-'.$eng, 'Editar '.$esp, $esp, '', ''];
        $permission[] = ['delete-'.$eng, 'Eliminar '.$esp, $esp, '', ''];

        $eng = 'zonas';
        $esp = 'Zonas';
        $permission[] = [$eng, $esp, $esp, $eng, 'Menu Zonas'];
        $permission[] = ['create-'.$eng, 'Crear '.$esp, $esp, '', ''];
        $permission[] = ['edit-'.$eng, 'Editar '.$esp, $esp, '', ''];
        $permission[] = ['delete-'.$eng, 'Eliminar '.$esp, $esp, '', ''];

        $eng = 'despachos';
        $esp = 'despachos';
        $permission[] = [$eng, $esp, $esp, $eng, 'Menu Despachos'];
        $permission[] = ['create-'.$eng, 'Crear '.$esp, $esp, '', ''];
        $permission[] = ['edit-'.$eng, 'Editar '.$esp, $esp, '', ''];
        $permission[] = ['delete-'.$eng, 'Eliminar '.$esp, $esp, '', ''];

        
        return $permission;
    }

    public function run()
    {
        $permissions = $this->getPermissions();

        foreach ($permissions as $permission) {
            if (! Permission::where('name', $permission[0])->exists()){
                Permission::firstOrCreate([
                    'name' => $permission[0],
                    'display_name' => $permission[1], 
                    'description' => $permission[2], 
                    'url'=>$permission[3],
                    'keywords'=>$permission[4]
                ]);        
            }            
        }

        $permissions = Permission::get();
        $admin = Role::where('name', 'admin')->first();
        if ($admin)
            $admin->syncPermissions($permissions);

        $this->command->info('Default permissions table seeded!');
    }
}
