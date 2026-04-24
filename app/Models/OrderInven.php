<?php

namespace App\Models;

use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\Model;

class OrderInven extends Model
{
    protected $connection = 'company';
    protected $table = 'INVEN';
    public $timestamps = false;

    protected $fillable = ['BASE2'];

    public function promocion()
    {
        return $this->belongsTo(Promocion::class, 'CODIGO', 'codigo');
    }

    public function fotos()
    {
        return $this->hasMany(InvenFoto::class, 'codigo', 'CODIGO');
    }

    public function informacion()
    {
        return $this->hasOne(InvenInformacion::class, 'codigo', 'CODIGO');
    }

    public function grupo()
    {
        return $this->belongsTo(OrderGrupo::class, 'CGRUPO', 'cgrupo');
    }

    public function artdepos()
    {
        return $this->hasMany(ArtDepos::class, 'CODIGO', 'CODIGO');
    }


    public function createNew($request)
    {
        return $this->insert([
            'CODIGO' => $request->CODIGO,
            'DESCR' => $request->DESCR
        ]);
    }

    public function deleteRecord($code)
    {
        return $this->where('CODIGO', $code)->delete();
    }

    public function getData($code = null)
    {
        if ($code) {
            /*
            return $this->with('informacion')
                ->select('INVEN.CODIGO', 'INVEN.DESCR', 'GRUPO.DGRUPO', 'ARTDEPOS.EUNIDAD', 'ARTDEPOS.ECAJA', 'FOTO', 'INVEN.BASE1', 'INVEN.BASE2', 'INVEN.ACTUALDL', 'INVEN.DUNIMEDD', 'INVEN.SMIN', 'INVEN.APLICDES')
                ->leftJoin('GRUPO', 'GRUPO.CGRUPO', 'INVEN.CGRUPO')
                ->leftJoin('ARTDEPOS', 'ARTDEPOS.CODIGO', 'INVEN.CODIGO')
                ->where('INVEN.CODIGO', $code)
                ->first();
            */
            return $this->with('informacion')
                ->select(
                    'INVEN.CODIGO',
                    'INVEN.DESCR',
                    'GRUPO.DGRUPO',
                    'sub.EUNIDAD',
                    'sub.ECAJA',
                    'FOTO',
                    'INVEN.BASE1',
                    'INVEN.BASE2',
                    'INVEN.ACTUALDL',
                    'INVEN.DUNIMEDD',
                    'INVEN.SMIN',
                    'INVEN.APLICDES'
                )
                ->leftJoin('GRUPO', 'GRUPO.CGRUPO', 'INVEN.CGRUPO')
                ->leftJoinSub(
                    DB::connection('company')->table('ARTDEPOS')
                        ->selectRaw('CODIGO, ANY_VALUE(EUNIDAD) as EUNIDAD, ANY_VALUE(ECAJA) as ECAJA')
                        ->groupBy('CODIGO'),
                    'sub',
                    function ($join) {
                        $join->on('sub.CODIGO', '=', 'INVEN.CODIGO');
                    }
                )
                ->where('INVEN.CODIGO', $code)
                ->first();
        }
        /*
        return $this->with('informacion')
            ->select('INVEN.CODIGO', 'INVEN.DESCR', 'GRUPO.DGRUPO', 'ARTDEPOS.EUNIDAD', 'ARTDEPOS.ECAJA', 'FOTO', 'INVEN.BASE1', 'INVEN.BASE2', 'INVEN.ACTUALDL', 'INVEN.DUNIMEDD', 'INVEN.SMIN', 'INVEN.APLICDES')
            ->leftJoin('GRUPO', 'GRUPO.CGRUPO', 'INVEN.CGRUPO')
            ->leftJoin('ARTDEPOS', 'ARTDEPOS.CODIGO', 'INVEN.CODIGO')
            ->orderBy('DESCR')
            ->get();
        */

        return $this->with('informacion')
            ->select(
                'INVEN.CODIGO',
                'INVEN.DESCR',
                'GRUPO.DGRUPO',
                'sub.EUNIDAD',
                'sub.ECAJA',
                'FOTO',
                'INVEN.BASE1',
                'INVEN.BASE2',
                'INVEN.ACTUALDL',
                'INVEN.DUNIMEDD',
                'INVEN.SMIN',
                'INVEN.APLICDES'
            )
            ->leftJoin('GRUPO', 'GRUPO.CGRUPO', 'INVEN.CGRUPO')
            ->leftJoinSub(
                DB::connection('company')->table('ARTDEPOS')
                    ->selectRaw('CODIGO, ANY_VALUE(EUNIDAD) as EUNIDAD, ANY_VALUE(ECAJA) as ECAJA')
                    ->groupBy('CODIGO'),
                'sub',
                function ($join) {
                    $join->on('sub.CODIGO', '=', 'INVEN.CODIGO');
                }
            )
            ->orderBy('DESCR')
            ->get();

    }

    public function getDataLimit($limit = 5)
    {
        /*
        return $this
            ->select('INVEN.CODIGO', 'INVEN.DESCR', 'GRUPO.DGRUPO', 'ARTDEPOS.EUNIDAD', 'ARTDEPOS.ECAJA', 'FOTO', 'INVEN.BASE1', 'INVEN.BASE2', 'INVEN.ACTUALDL', 'INVEN.DUNIMEDD')
            ->leftJoin('GRUPO', 'GRUPO.CGRUPO', 'INVEN.CGRUPO')
            ->leftJoin('ARTDEPOS', 'ARTDEPOS.CODIGO', 'INVEN.CODIGO')
            ->orderBy('DESCR')
            ->get()
            ->take($limit);
        */

        return $this->with('informacion')
            ->select(
                'INVEN.CODIGO',
                'INVEN.DESCR',
                'GRUPO.DGRUPO',
                'sub.EUNIDAD',
                'sub.ECAJA',
                'FOTO',
                'INVEN.BASE1',
                'INVEN.BASE2',
                'INVEN.ACTUALDL',
                'INVEN.DUNIMEDD',
                'INVEN.SMIN',
                'INVEN.APLICDES'
            )
            ->leftJoin('GRUPO', 'GRUPO.CGRUPO', 'INVEN.CGRUPO')
            ->leftJoinSub(
                DB::connection('company')->table('ARTDEPOS')
                    ->selectRaw('CODIGO, ANY_VALUE(EUNIDAD) as EUNIDAD, ANY_VALUE(ECAJA) as ECAJA')
                    ->groupBy('CODIGO'),
                'sub',
                function ($join) {
                    $join->on('sub.CODIGO', '=', 'INVEN.CODIGO');
                }
            )
            ->orderBy('DESCR')
            ->get()
            ->take($limit);
    }

    public function getProduct($code)
    {
        /*
        return $this
            ->select('INVEN.CODIGO', 'INVEN.DESCR', 'GRUPO.DGRUPO', 'ARTDEPOS.EUNIDAD', 'ARTDEPOS.ECAJA', 'FOTO', 'INVEN.BASE1', 'INVEN.BASE2', 'INVEN.ACTUALDL', 'INVEN.DUNIMEDD', 'INVEN.CGRUPO', 'INVEN.IMPUEST')
            ->leftJoin('GRUPO', 'GRUPO.CGRUPO', 'INVEN.CGRUPO')
            ->leftJoin('ARTDEPOS', 'ARTDEPOS.CODIGO', 'INVEN.CODIGO')
            ->where('INVEN.CODIGO', $code)
            ->first();
            */

        return $this
            ->select(
                'INVEN.CODIGO',
                'INVEN.DESCR',
                'GRUPO.DGRUPO',
                'sub.EUNIDAD',
                'sub.ECAJA',
                'FOTO',
                'INVEN.BASE1',
                'INVEN.BASE2',
                'INVEN.ACTUALDL',
                'INVEN.DUNIMEDD',
                'INVEN.CGRUPO',
                'INVEN.IMPUEST',
                'INVEN.SMIN',
                'INVEN.APLICDES'
            )
            ->leftJoin('GRUPO', 'GRUPO.CGRUPO', 'INVEN.CGRUPO')
            ->leftJoinSub(
                DB::connection('company')->table('ARTDEPOS')
                    ->selectRaw('CODIGO, ANY_VALUE(EUNIDAD) as EUNIDAD, ANY_VALUE(ECAJA) as ECAJA')
                    ->groupBy('CODIGO'),
                'sub',
                function ($join) {
                    $join->on('sub.CODIGO', '=', 'INVEN.CODIGO');
                }
            )
            ->where('INVEN.CODIGO', $code)
            ->first();
    }

    public function getGroupProducts($vendedor, $cdepos, $cgrupo = null, $search = null)
    {
        $query = $this->with('promocion', 'fotos', 'informacion')
            ->join('GRUPO', 'GRUPO.CGRUPO', '=', 'INVEN.CGRUPO')
            ->join('ARTDEPOS', 'ARTDEPOS.CODIGO', '=', 'INVEN.CODIGO')
            ->where('ARTDEPOS.CDEPOS', $cdepos)
            ->where('ARTDEPOS.EUNIDAD', '>=', DB::raw('INVEN.SMIN'));

        if ($cgrupo && $cgrupo != 'TODOS') {
            $query->where('INVEN.CGRUPO', $cgrupo);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('INVEN.DESCR', 'like', '%' . $search . '%')
                    ->orWhere('INVEN.CODIGO', 'like', '%' . $search . '%');
            });
        }

        return $query->orderBy('DESCR')->get();
    }

    public function getGroupProductsByCode($vendedor, $cdepos, $cgrupo = 'TODOS', $code = null)
    {
        if ($code != null) {
            return $this->with('promocion')->with('fotos')->with('informacion')
                ->leftJoin('GRUPO', 'GRUPO.CGRUPO', 'INVEN.CGRUPO')
                ->join('ARTDEPOS', 'ARTDEPOS.CODIGO', 'INVEN.CODIGO')
                ->where('ARTDEPOS.CDEPOS', $cdepos)
                ->where('ARTDEPOS.EUNIDAD', '>=', 'INVEN.SMIN')
                ->where('INVEN.CODIGO', $code)
                ->orderBy('DESCR')
                ->first();
        }
    }

    public function getGroupProductsByDepos($cdepos, $cgrupo = null)
    {
        if ($cgrupo && $cgrupo != 'TODOS') {
            return $this->with('promocion')->with('fotos')->with('informacion')
                ->leftJoin('GRUPO', 'GRUPO.CGRUPO', 'INVEN.CGRUPO')
                ->join('ARTDEPOS', 'ARTDEPOS.CODIGO', 'INVEN.CODIGO')
                ->where('INVEN.CGRUPO', $cgrupo)
                ->where('ARTDEPOS.CDEPOS', $cdepos)
                /*
                ->whereIn('ARTDEPOS.CDEPOS', function($query) use($vendedor) {
                $query->select('CDEPOS')->from('vendedor_deposito')
                    ->where('vendedor_id', $vendedor->id);
                })
                */
                ->where('ARTDEPOS.EUNIDAD', '>=', 'INVEN.SMIN')
                ->orderBy('DESCR')
                ->get();
        }
        return $this->with('promocion')->with('fotos')->with('informacion')
            ->join('GRUPO', 'GRUPO.CGRUPO', 'INVEN.CGRUPO')
            ->join('ARTDEPOS', 'ARTDEPOS.CODIGO', 'INVEN.CODIGO')
            ->where('ARTDEPOS.CDEPOS', $cdepos)
            /*
            ->whereIn('ARTDEPOS.CDEPOS', function($query) use($vendedor) {
                $query->select('CDEPOS')->from('vendedor_deposito')
                    ->where('vendedor_id', $vendedor->id);
            })
            */
            ->where('ARTDEPOS.EUNIDAD', '>=', 'INVEN.SMIN')
            ->orderBy('DESCR')
            ->get();
    }

    public function updateItem($code, $photo)
    {
        return $this->where('CODIGO', $code)->update([
            //'CODIGO' => $request->CODIGO,
            //'DESCR' => $request->DESCR
            'FOTO' => $photo
        ]);
    }

    public function updatePrices($code, $base1 = null, $base2 = null)
    {
        $data = [];
        if ($base1) {
            $data['BASE1'] = $base1;
        }
        if ($base2) {
            $data['BASE2'] = $base2;
        }
        if (count($data) > 0) {
            return $this->where('CODIGO', $code)->update($data);
        }
        return false;
    }

    public function updateItemSmin($code, $request)
    {
        $aplicdes = isset($request->aplicdes) && $request->aplicdes == 1 ? 1 : 0;
        return $this->where('CODIGO', $code)->update([
            'SMIN' => $request->SMIN ? $request->SMIN : 1,
            'APLICDES' => $aplicdes
        ]);
    }

    /**
     * Búsqueda flexible: por nombre (DESCR) o grupo (DGRUPO)
     */
    public function getGroupProductsFlexible($vendedor, $cdepos, $cgrupo = null, $search = null)
    {
        $query = $this->with('promocion', 'fotos', 'informacion')
            ->join('GRUPO', 'GRUPO.CGRUPO', '=', 'INVEN.CGRUPO')
            ->join('ARTDEPOS', 'ARTDEPOS.CODIGO', '=', 'INVEN.CODIGO')
            ->where('ARTDEPOS.CDEPOS', $cdepos)
            ->where('ARTDEPOS.EUNIDAD', '>=', DB::raw('INVEN.SMIN'));

        if ($cgrupo && $cgrupo != 'TODOS') {
            $query->where('INVEN.CGRUPO', $cgrupo);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('INVEN.DESCR', 'like', '%' . $search . '%')
                    ->orWhere('INVEN.CODIGO', 'like', '%' . $search . '%')
                    ->orWhere('GRUPO.DGRUPO', 'like', '%' . $search . '%');
            });
        }

        return $query->orderBy('DESCR')->get();
    }

    public function getReportConfig()
    {
        return [
            'title' => 'Listado de Productos',
            'company' => Auth::user()->company
        ];
    }

}
