<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use DataTables;

trait ExpenseTrait
{

    private function getJsonOrDatatableResponse(Request $request, $data)
    {
        if ($request->datatable) {
            return $this->getDatatableResponse($request, $data);
        }        
        //return $this->getJsonResponse($request, $data);
    }

    private function getDatatableResponse($request, $data)
    {
        return DataTables::of($data)
            ->addColumn('action', function ( $data ) {
                return  $this->getActionColumn($data);
            })
            ->editColumn('date_at', function ( $data ) {
                return  $this->getDateAtColumn($data);
            })
            ->editColumn('amount', function ( $data ) {
                return  $this->getAmountColumn($data);
            })
            ->editColumn('dollar_amount', function ( $data ) {
                return  $this->getDollarAmountColumn($data);
            })
            ->editColumn('expense_group', function ( $data ) {
                return  $this->getExpenseGroupColumn($data);
            })
            ->rawColumns(['action', 'date_at', 'amount', 'dollar_amount', 'expense_group'])
            ->make(true);
    }

    private static function getDateAtColumn($data)
    {
        return formatoFechaDMASimple($data->date_at);
    }

    private static function getAmountColumn($data)
    {
        return '<span class="badge badge-primary">Bs. ' . number_format((float)$data->amount, 2, ',', '.') . '</span>';
    }

    private static function getDollarAmountColumn($data)
    {
        return '<span class="badge badge-success">$ ' . number_format((float)$data->dollar_amount, 2, ',', '.') . '</span>';
    }

    private static function getExpenseGroupColumn($data)
    {
        return @$data->expense_group->name;
    }

    public static function getActionColumn($data)
    {
        $buttons = [];
        if (hasExpensePermission()) {
            $buttons[] = [
                'style' => 'primary btn-sm',
                'name' => 'edit',
                'hint' => 'Editar',
                'icon' => 'edit',
            ];

            $buttons[] = [
                'style' => 'danger btn-sm',
                'name' => 'delete',
                'hint' => 'Eliminar',
                'icon' => 'trash',
            ];
        }

        return getActionHtmlColumn ($data, $buttons);
    }
    
}
