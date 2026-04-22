<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Models\Vendedor;
use App\Models\VendedorDeposito;
use App\Models\OrderInven;
use App\Models\DescuentoGlobal;
use Illuminate\Support\Facades\Auth;

class ProductListExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    protected $cdepos;
    protected $cgrupo;
    protected $search;

    public function __construct($cdepos = null, $cgrupo = null, $search = null)
    {
        $this->cdepos = $cdepos;
        $this->cgrupo = $cgrupo;
        $this->search = $search;
    }

    public function collection()
    {
        $vendedor = Vendedor::where('email', auth()->user()->email)->first();
        if (!$vendedor) {
            return collect();
        }

        $orderInven = new OrderInven();
        $products = $orderInven->getGroupProducts($vendedor, $this->cdepos, $this->cgrupo, $this->search);
        
        $config = DescuentoGlobal::first();
        $show_precio1 = $config && $config->show_precio1 == 'SI' ? 'SI' : 'NO';
        $descuento = $config ? (-1)*$config->discount : 0;
        
        $data = [];
        
        // Get product groups for special handling
        $productos_nacionales = \App\Models\OrderGrupo::where('DGRUPO', 'like', '%NACIONAL%')->first();
        $productos_lamina = \App\Models\OrderGrupo::where('DGRUPO', 'LAMINAS')->first();
        
        foreach ($products as $product) {
            $precio1 = 0;
            $precio2 = 0;
            $porcentaje_iva = isset($product->IMPUEST) && $product->IMPUEST > 0 ? $product->IMPUEST / 100 : 0;
            
            // Check if product is in national or lamina group
            $is_nacional = $productos_nacionales && $product->CGRUPO == $productos_nacionales->CGRUPO;
            $is_lamina = $productos_lamina && $product->CGRUPO == $productos_lamina->CGRUPO;
            
            // Base price calculation
            if (isset($product->BASE2) && $product->BASE2 > 0) {
                $precio2 = (float)$product->BASE2;
                $precio1 = $precio2;
                $precio2 = $precio2 - ($precio2 * $descuento / 100);
                
                // For national products, swap BASE1 and BASE2
                if ($is_nacional) {
                    $precio1 = (float)$product->BASE2;
                    $precio2 = (float)$product->BASE1;
                }
            } else {
                $precio1 = (float)($product->BASE1 ?? 0);
                $precio2 = (float)($product->BASE2 ?? 0);
            }
            
            // Apply discount if needed
            if ($show_precio1 == 'NO') {
                //$precio2 = $precio1 - ($precio1 * ($descuento / 100));
            }

            $precio1 += $precio1 * $porcentaje_iva;
            $precio2 += $precio2 * $porcentaje_iva;
            
            // Get minimum stock from product information
            $stock_minimo = 0;
            if (isset($product->informacion) && $product->informacion && $product->informacion->stock_minimo > 0) {
                $stock_minimo = (int)$product->informacion->stock_minimo;
            } elseif (isset($product->SMIN) && $product->SMIN > 0) {
                $stock_minimo = (int)$product->SMIN;
            }
            if ($precio2>0){
                $data[] = [
                    'Código' => $product->CODIGO ?? '',
                    'Descripción' => $product->DESCR ?? '',
                    'Cantidad Mínima' => $stock_minimo,
                    'Precio 1' => number_format($precio1, 2, ',', '.'),
                    'Precio 2' => number_format($precio2, 2, ',', '.'),
                ];
            }
        }
        
        return collect($data);
    }

    public function headings(): array
    {
        return [
            'Código',
            'Descripción',
            'Cantidad Mínima',
            'Precio 1',
            'Precio 2',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
