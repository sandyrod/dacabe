<?php
	namespace App\Http\Controllers;

	use App\{User};
	use App\Models\{Noticia, Vendedor, Deposito, OrderInven, OrderGrupo};
	use Illuminate\Http\Request;
	use DB;
	
	class LandingController extends Controller
	{
	    public function index(Request $request, $goto='')
	    {
	    	$modo = '';
	    	if ($modo!='test') 
	    		return view('landing.index')->withGoto($goto);
	    	
	       	return view('const');
	 	}

	    public function dacabe(Request $request, $goto='')
	    {
	    	return view('landing.index')->withGoto($goto);
	 	}

		public function privacy()
	    {
	    	return view('landing.privacy');
	 	}

		public function vendedores(Request $request)
	    {
	    	$sellers = (new Vendedor)->getData();
	    	foreach($sellers as $seller){
	    		$zona = '';
		    	if (@$seller->depositos) {
		            foreach($seller->depositos as $deposito) {
		                $dep = (new Deposito)->where('CDEPOS', $deposito->CDEPOS)->first();
		                if ($dep) {
		                    $zona .= $dep->DDEPOS . ', ';
		                }
		            }
		            $seller->zona = substr($zona, 0, strlen($zona)-2);
		        }	    		
	    	}

	    	return view('landing.sellers')->withSellers($sellers);
	 	}

	 	public function vendedor(Request $request, $codigo_vendedor)
	    {
	    	$seller = (new Vendedor)->where('codigo', $codigo_vendedor)->first();
	    	if ($seller){
	    		$seller->user = (new User)->where('email', $seller->email)->first();
	    	}
	    	
	    	return view('landing.seller', compact(['seller']));
	 	}

	 	public function catalogo(Request $request, $vendedor_id)
	    {
	    	$seller = (new Vendedor)->find($vendedor_id);
	    	$user = (new User)->where('email', $seller->email)->first();
	    	//$sellers = (new Vendedor)->getData(@$vendedor->user_id);
			$categoria = $request->categoria ? $request->categoria : 'TODOS';
			$search_descr = $request->input('search_descr');
			// Buscar por nombre o grupo
			$products = (new OrderInven)->getGroupProductsFlexible($seller->id, $seller->CDEPOS, $categoria, $search_descr);
			$categories = (new OrderGrupo)->getData();
			$grupo = @$categoria ? (new OrderGrupo)->where('CGRUPO', $categoria)->first() : null;
			$category = $grupo ? $grupo->DGRUPO : '';

			return view('landing.products', compact(['seller', 'products', 'categories', 'category', 'user']));
	 	}
	 	
		public function catalogoFull(Request $request)
	    {
	    	$seller = (new Vendedor)->find(1);
	    	$user = (new User)->where('email', $seller->email)->first();
	    	//$sellers = (new Vendedor)->getData(@$vendedor->user_id);
	    	$categoria = $request->categoria ? $request->categoria : 'TODOS';
	    	$search_descr = $request->input('search_descr');
			$search_dgrupo = $request->input('search_dgrupo');

			// Si hay filtro por grupo (DGRUPO), buscar el CGRUPO correspondiente
			if ($search_dgrupo) {
				$grupoObj = (new OrderGrupo)->where('DGRUPO', $search_dgrupo)->first();
				$categoria = $grupoObj ? $grupoObj->CGRUPO : null;
			}

			// Si hay filtro por nombre o grupo, pasar el search al modelo
			if ($search_descr || $search_dgrupo) {
				$products = (new OrderInven)->getGroupProducts($seller->id, $seller->CDEPOS, $categoria, $search_descr);
			} else {
				$products = (new OrderInven)->getGroupProducts($seller->id, $seller->CDEPOS, $categoria);
			}
	    	$categories = (new OrderGrupo)->getData();
	    	$grupo = @$categoria ? (new OrderGrupo)->where('CGRUPO', $categoria)->first() : null;
	    	$category = $grupo ? $grupo->DGRUPO : '';

	    	return view('landing.products', compact(['seller', 'products', 'categories', 'category', 'user']));
	 	}
		
		public function catalogoFullRecargo(Request $request)
	    {
	    	$seller = (new Vendedor)->find(3);
	    	$user = (new User)->where('email', $seller->email)->first();
	    	//$sellers = (new Vendedor)->getData(@$vendedor->user_id);
	    	$categoria = $request->categoria ? $request->categoria : 'TODOS';
	    	$search_descr = $request->input('search_descr');
			$search_dgrupo = $request->input('search_dgrupo');

			// Si hay filtro por grupo (DGRUPO), buscar el CGRUPO correspondiente
			if ($search_dgrupo) {
				$grupoObj = (new OrderGrupo)->where('DGRUPO', $search_dgrupo)->first();
				$categoria = $grupoObj ? $grupoObj->CGRUPO : null;
			}

			// Si hay filtro por nombre o grupo, pasar el search al modelo
			if ($search_descr || $search_dgrupo) {
				$products = (new OrderInven)->getGroupProducts($seller->id, $seller->CDEPOS, $categoria, $search_descr);
			} else {
				$products = (new OrderInven)->getGroupProducts($seller->id, $seller->CDEPOS, $categoria);
			}
	    	$categories = (new OrderGrupo)->getData();
	    	$grupo = @$categoria ? (new OrderGrupo)->where('CGRUPO', $categoria)->first() : null;
	    	$category = $grupo ? $grupo->DGRUPO : '';

	    	return view('landing.products', compact(['seller', 'products', 'categories', 'category', 'user']));
	 	}

		public function catalogoCategoria(Request $request, $categoria)
	    {
	    	$seller = (new Vendedor)->find(1);
	    	$user = (new User)->where('email', $seller->email)->first();
	    	$categoria = $request->categoria ? $request->categoria : 'TODOS';
	    	$products = (new OrderInven)->getGroupProducts($seller->id, $seller->CDEPOS, $categoria);
	    	$categories = (new OrderGrupo)->getData();
	    	$grupo = @$categoria ? (new OrderGrupo)->where('CGRUPO', $categoria)->first() : null;
	    	$category = $grupo ? $grupo->DGRUPO : '';

	    	return view('landing.products_lists', compact(['seller', 'products', 'categories', 'category', 'user']));
	 	}

		public function catalogoProducto(Request $request, $producto)
	    {
	    	$seller = (new Vendedor)->find(1);
	    	$user = (new User)->where('email', $seller->email)->first();
	    	$producto = $request->producto ? $request->producto : 'TODOS';
	    	$products = (new OrderInven)->getGroupProductsByCode($seller->id, $seller->CDEPOS, 'TODOS', $producto);
	    	$categories = (new OrderGrupo)->getData();
	    	$grupo = @$categoria ? (new OrderGrupo)->where('CGRUPO', $categoria)->first() : null;
	    	$category = $grupo ? $grupo->DGRUPO : '';

	    	return view('landing.products_lists', compact(['seller', 'products', 'categories', 'category', 'user']));
	 	}

	 	public function getCalendarDetail(Request $request)
	 	{
	 		$calendar = $header = array();
	 		$calendar = $this->getCalendar($request);	    		
	    	$header = $this->getHeader();	  

	    	return response()->json([
                'status' => true,
                'controller' => 'Landing',
                'calendar' => $calendar,
                'header' => $header,
                'type' => 'success'
            ],200);  		
	 	}

	 	private function getHeader()
	 	{
	 		$header = array(); 
			$header[] = $this->getCalendarCell("imperative", "", "L", "Lunes");
			$header[] = $this->getCalendarCell("imperative", "", "M", "Martes");
			$header[] = $this->getCalendarCell("imperative", "", "M", "Miercoles");
			$header[] = $this->getCalendarCell("imperative", "", "J", "Jueves");
			$header[] = $this->getCalendarCell("imperative", "", "V", "Viernes");
			$header[] = $this->getCalendarCell("imperative", "", "S", "Sabado");
			$header[] = $this->getCalendarCell("imperative", "", "D", "Domingo");

			return $header;
		}

	 	private function getCalendar($request)
	 	{
	 		$now = \Carbon\Carbon::now();
			$month = ($request->month) ? $request->month: $now->month;
			$year = $now->year;
			$rif = ($request->rif) ? $request->rif : '';
			$seniat = $this->getDeclarations($month);

			$calendar = array();
	 		$first_day_month = date('w',mktime(0,0,0,$month,1,$year));
	 		$first_day_month = ($first_day_month==0) ? "7" : $first_day_month;
		    $days_in_month = date('t',mktime(0,0,0,$month,1,$year));
			$days_in_last_month = date('t',mktime(0,0,0,$month-1,1,$year));
   		    
   		    $months = array();
   		    $diff = $days_in_last_month - ($first_day_month-1);
   		    for ($i=1; $i<$first_day_month; $i++) {
   		    	$diff++;
   		    	$calendar[] = $this->getCalendarCell("declarative", $diff, "", "");
   		    }

   		    $n = sizeof($seniat);
   		    for ($j=1; $j <= $days_in_month; $j++) { 
   		    	$cad = $symbol = $name = '';
   		    	$style = 'concurrent';
   		    	for ($i=0; $i < $n; $i++) { 
   		    		if ($j==$seniat[$i][1]){
   		    			$style = $this->getStyle($seniat[$i][0]);
   		    			$rif_arr=(substr($seniat[$i][0], 0, 1)=='_') ? substr($seniat[$i][0], 1) : $seniat[$i][0];
	 					
   		    			if (! $rif || ($rif==$rif_arr)) {
	   		    			if (substr($seniat[$i][0], 0,1)=='_')
	   		    				$name .= '<span class="'.$style.'-icon"><i class="fa fa-circle"></i></span> ';
	   		    			else
	   		    				$symbol.='<span class="'.$style.'-icon"><i class="fa fa-bell"></i></span> ';
   		    			}
   		    		}
   		    	}
   		    	$calendar[] = $this->getCalendarCell('concurrent', $j, $symbol, $name);

   		    }
			
			//$month = $this->getMonth($seniat, $month, $year);

			return $calendar;
	 	}

	 	private function getStyle($rif)
	 	{
	 		if (substr($rif, 0, 1) == '_')
	 			$rif = substr($rif, 1);
	 		if ($rif=='05')
	 			return 'scripting';
	 		if ($rif=='69')
	 			return 'object-oriented';
	 		if ($rif=='37')
	 			return 'functional';
	 		if ($rif=='48')
	 			return 'multi-paradigm';
	 		if ($rif=='12')
	 			return 'mechanical';
	 		return 'concurrent';
	 	}

	 	private function setDeclarations()
	 	{
	 		$months = array();
	 		$i=0;
	 		$m=3;
	 		$months[$m][$i++] = ["05", 6];
	 		$months[$m][$i++] = ["05", 12];
	 		$months[$m][$i++] = ["05", 18];
	 		$months[$m][$i++] = ["05", 26];
	 		$months[$m][$i++] = ["69", 5];
	 		$months[$m][$i++] = ["69", 13];
	 		$months[$m][$i++] = ["69", 17];
	 		$months[$m][$i++] = ["69", 25];
	 		$months[$m][$i++] = ["37", 4];
	 		$months[$m][$i++] = ["37", 9];
	 		$months[$m][$i++] = ["37", 20];
	 		$months[$m][$i++] = ["37", 27];
	 		$months[$m][$i++] = ["48", 3];
	 		$months[$m][$i++] = ["48", 10];
	 		$months[$m][$i++] = ["48", 16];
	 		$months[$m][$i++] = ["48", 24];
	 		$months[$m][$i++] = ["48", 30];
	 		$months[$m][$i++] = ["12", 2];
	 		$months[$m][$i++] = ["12", 11];
	 		$months[$m][$i++] = ["12", 16];
	 		$months[$m][$i++] = ["12", 23];
	 		$months[$m][$i++] = ["12", 31];
	 		$i=0;
	 		$m=4;
	 		$months[$m][$i++] = ["05", 1];
	 		$months[$m][$i++] = ["05", 7];
	 		$months[$m][$i++] = ["05", 13];
	 		$months[$m][$i++] = ["05", 23];
	 		$months[$m][$i++] = ["05", 29];
	 		$months[$m][$i++] = ["_05", 23];				
			$months[$m][$i++] = ["69", 2];
			$months[$m][$i++] = ["69", 8];
	 		$months[$m][$i++] = ["69", 17];
	 		$months[$m][$i++] = ["69", 24];
	 		$months[$m][$i++] = ["69", 30];
			$months[$m][$i++] = ["_69", 17];
			$months[$m][$i++] = ["37", 3];
			$months[$m][$i++] = ["37", 8];
	 		$months[$m][$i++] = ["37", 14];
	 		$months[$m][$i++] = ["37", 22];
	 		$months[$m][$i++] = ["37", 27];
	 		$months[$m][$i++] = ["_37", 22];
			$months[$m][$i++] = ["48", 7];
	 		$months[$m][$i++] = ["48", 15];
	 		$months[$m][$i++] = ["48", 21];
	 		$months[$m][$i++] = ["48", 28];
	 		$months[$m][$i++] = ["_48", 21];
			$months[$m][$i++] = ["12", 6];
	 		$months[$m][$i++] = ["12", 16];
	 		$months[$m][$i++] = ["12", 20];
	 		$months[$m][$i++] = ["12", 27];
	 		$months[$m][$i++] = ["_12", 20];
	 		$i=0;
	 		$m=5;
	 		$months[$m][$i++] = ["05", 7];
	 		$months[$m][$i++] = ["05", 14];
	 		$months[$m][$i++] = ["05", 19];
	 		$months[$m][$i++] = ["05", 28];				
	 		$months[$m][$i++] = ["_05", 19];				
			$months[$m][$i++] = ["69", 8];
	 		$months[$m][$i++] = ["69", 15];
	 		$months[$m][$i++] = ["69", 20];
	 		$months[$m][$i++] = ["69", 27];				    
	 		$months[$m][$i++] = ["_69", 20];				    
			$months[$m][$i++] = ["37", 6];
	 		$months[$m][$i++] = ["37", 11];
	 		$months[$m][$i++] = ["37", 21];
	 		$months[$m][$i++] = ["37", 29];
	 		$months[$m][$i++] = ["_37", 21];
			$months[$m][$i++] = ["48", 4];
	 		$months[$m][$i++] = ["48", 12];
	 		$months[$m][$i++] = ["48", 22];
	 		$months[$m][$i++] = ["48", 29];
	 		$months[$m][$i++] = ["_48", 22];
			$months[$m][$i++] = ["12", 5];
	 		$months[$m][$i++] = ["12", 13];
	 		$months[$m][$i++] = ["12", 18];
	 		$months[$m][$i++] = ["12", 26];
	 		$months[$m][$i++] = ["_12", 26];
	 		$i=0;
	 		$m=6;
	 		$months[$m][$i++] = ["05", 2];
	 		$months[$m][$i++] = ["05", 10];
	 		$months[$m][$i++] = ["05", 16];
	 		$months[$m][$i++] = ["05", 25];				
	 		$months[$m][$i++] = ["_05", 25];				
			$months[$m][$i++] = ["69", 1];
	 		$months[$m][$i++] = ["69", 9];
	 		$months[$m][$i++] = ["69", 16];
	 		$months[$m][$i++] = ["69", 23];				    
	 		$months[$m][$i++] = ["_69", 23];				    
			$months[$m][$i++] = ["37", 3];
	 		$months[$m][$i++] = ["37", 8];
	 		$months[$m][$i++] = ["37", 19];
	 		$months[$m][$i++] = ["37", 26];
	 		$months[$m][$i++] = ["_37", 19];
			$months[$m][$i++] = ["48", 5];
	 		$months[$m][$i++] = ["48", 12];
	 		$months[$m][$i++] = ["48", 17];
	 		$months[$m][$i++] = ["48", 22];
	 		$months[$m][$i++] = ["48", 30];
	 		$months[$m][$i++] = ["_48", 22];
			$months[$m][$i++] = ["12", 4];
	 		$months[$m][$i++] = ["12", 11];
	 		$months[$m][$i++] = ["12", 18];
	 		$months[$m][$i++] = ["12", 26];
	 		$months[$m][$i++] = ["_12", 18];
	 		$i=0;
	 		$m=7;
	 		$months[$m][$i++] = ["05", 3];
	 		$months[$m][$i++] = ["05", 10];
	 		$months[$m][$i++] = ["05", 17];
	 		$months[$m][$i++] = ["05", 23];				
	 		$months[$m][$i++] = ["05", 30];				
	 		$months[$m][$i++] = ["_05", 17];				
			$months[$m][$i++] = ["69", 6];
	 		$months[$m][$i++] = ["69", 13];
	 		$months[$m][$i++] = ["69", 20];
	 		$months[$m][$i++] = ["69", 29];				    
	 		$months[$m][$i++] = ["_69", 20];				    
			$months[$m][$i++] = ["37", 2];
	 		$months[$m][$i++] = ["37", 7];
	 		$months[$m][$i++] = ["37", 14];
	 		$months[$m][$i++] = ["37", 21];
	 		$months[$m][$i++] = ["37", 28];
	 		$months[$m][$i++] = ["_37", 21];
			$months[$m][$i++] = ["48", 8];
	 		$months[$m][$i++] = ["48", 16];
	 		$months[$m][$i++] = ["48", 22];
	 		$months[$m][$i++] = ["48", 27];	 		
	 		$months[$m][$i++] = ["_48", 22];
			$months[$m][$i++] = ["12", 1];
	 		$months[$m][$i++] = ["12", 9];
	 		$months[$m][$i++] = ["12", 15];
	 		$months[$m][$i++] = ["12", 23];
	 		$months[$m][$i++] = ["12", 31];
	 		$months[$m][$i++] = ["_12", 23];
	 		$i=0;
	 		$m=8;
	 		$months[$m][$i++] = ["05", 4];
	 		$months[$m][$i++] = ["05", 11];
	 		$months[$m][$i++] = ["05", 18];
	 		$months[$m][$i++] = ["05", 25];				
	 		$months[$m][$i++] = ["_05", 25];
			$months[$m][$i++] = ["69", 3];
	 		$months[$m][$i++] = ["69", 10];
	 		$months[$m][$i++] = ["69", 17];
	 		$months[$m][$i++] = ["69", 24];				    
	 		$months[$m][$i++] = ["69", 31];				    
	 		$months[$m][$i++] = ["_69", 24];				    
			$months[$m][$i++] = ["37", 7];
	 		$months[$m][$i++] = ["37", 12];
	 		$months[$m][$i++] = ["37", 21];
	 		$months[$m][$i++] = ["37", 26];
	 		$months[$m][$i++] = ["_37", 21];
			$months[$m][$i++] = ["48", 6];
	 		$months[$m][$i++] = ["48", 14];
	 		$months[$m][$i++] = ["48", 20];
	 		$months[$m][$i++] = ["48", 28];	 		
	 		$months[$m][$i++] = ["_48", 20];
			$months[$m][$i++] = ["12", 5];
	 		$months[$m][$i++] = ["12", 13];
	 		$months[$m][$i++] = ["12", 19];
	 		$months[$m][$i++] = ["12", 27];
	 		$months[$m][$i++] = ["_12", 19];
	 		$i=0;
	 		$m=9;
	 		$months[$m][$i++] = ["05", 18];				
	 		//$months[$m][$i++] = ["_05", 18];
	 		$months[$m][$i++] = ["69", 21];
	 		//$months[$m][$i++] = ["_69", 21];
	 		$months[$m][$i++] = ["37", 22];
	 		//$months[$m][$i++] = ["_37", 22];
	 		$months[$m][$i++] = ["48", 23];	 		
	 		//$months[$m][$i++] = ["_48", 23];
	 		$months[$m][$i++] = ["12", 24];
	 		//$months[$m][$i++] = ["_12", 24];
	 		$i=0;
	 		$m=10;
	 		$months[$m][$i++] = ["05", 23];				
	 		$months[$m][$i++] = ["_05", 5];
	 		$months[$m][$i++] = ["69", 22];
	 		$months[$m][$i++] = ["_69", 2];
	 		$months[$m][$i++] = ["37", 21];
	 		$months[$m][$i++] = ["_37", 8];
	 		$months[$m][$i++] = ["48", 20];
	 		$months[$m][$i++] = ["_48", 7];
	 		$months[$m][$i++] = ["12", 19];
	 		$months[$m][$i++] = ["_12", 6];
	 		$i=0;
	 		$m=11;
	 		$months[$m][$i++] = ["05", 18];				
	 		$months[$m][$i++] = ["_05", 5];
	 		$months[$m][$i++] = ["69", 19];
	 		$months[$m][$i++] = ["_69", 6];
	 		$months[$m][$i++] = ["37", 20];
	 		$months[$m][$i++] = ["_37", 9];
	 		$months[$m][$i++] = ["48", 24];	
	 		$months[$m][$i++] = ["_48", 3];
	 		$months[$m][$i++] = ["12", 25];
	 		$months[$m][$i++] = ["_12", 4];
	 		$i=0;
	 		$m=12;
	 		$months[$m][$i++] = ["05", 18];				
	 		$months[$m][$i++] = ["_05", 2];
	 		$months[$m][$i++] = ["69", 17];
	 		$months[$m][$i++] = ["_69", 8];
	 		$months[$m][$i++] = ["37", 21];
	 		$months[$m][$i++] = ["_37", 7];
	 		$months[$m][$i++] = ["48", 23];	
	 		$months[$m][$i++] = ["_48", 3];
	 		$months[$m][$i++] = ["12", 22];
	 		$months[$m][$i++] = ["_12", 4];

	 		return $months;
	 	}

	 	private function getDeclarations($month)
	 	{
	 		$months=$this->setDeclarations();
	 		
			return $months[$month];
	 	}

	 	private function getMonth($seniat,$month,$year)
	 	{
	 		$now = \Carbon\Carbon::now();
	 		$month;
	 		$first_day_month = date('w',mktime(0,0,0,$month,1,$year));
		    //$running_day = ($running_day > 0) ? $running_day-1 : $running_day;
		    $days_in_month = date('t',mktime(0,0,0,$month,1,$year));

		    $days_in_last_month = date('t',mktime(0,0,0,$month-1,1,$year));
   		    //$nombre_dia=$day=date('w', strtotime($now));

   		    //dd(date("w",mktime(0,0,0,$month,1,$year)));
   		    
   		    $months = array();
   		    $diff = $days_in_last_month - ($first_day_month-1);
   		    for ($i=1; $i<$first_day_month; $i++) {
   		    	$months[] = [
				    "month" => $month,
				    "last_month_days" => $days_in_last_month,
				    "first_day_month" => $first_day_month,
				    "day" => "imperative",
				    "number" => $diff++,
				    "symbol" => "",
				    "name" => "",
				];
   		    }
			return $months;
	 	}

	 	private function getCalendarCell($style, $number, $symbol, $name='')
	 	{
	 		$cell = array();			 
			$cell = [
			    "style" => $style,
			    "number" => $number,
			    "symbol" => $symbol,
			    "name" => $name,
			];
			return $cell;
	 	}

	 	public function getLandingNotices() {
	    	$noticia = new Noticia();
                
			return response()->json([
                'status' => true,
                'controller'  => 'Noticias',
                'title'  => 'Operación Exitosa!',
                'notices' => $noticia->getData(),
                'type' => 'success'
            ],200);
	 	}

	 	
	 	

	 	public function getnotifppal(Request $request){
        if ($request->ajax() || $request->wantsJson()) {
            $registro = Noticia::where('seccion','principal')->first();
            if (count($registro)){
                $info = $registro->resumen;
                $fecha = \Carbon\Carbon::createFromFormat('Y-m-d', $registro->publicacion)->diffForHumans();
            }else{
                $info = 'Bienvenidos a la Aplicaci&oacute;n ControlWeb, visite www.cedano.com.ve para mayor informaci&oacute;n.';
                $fecha = \Carbon\Carbon::createFromFormat('Y-m-d', '2018-01-01')->diffForHumans();
            }

            return response()->json([
                'status' => true,
                'controller'  => 'Noticias',
                'title'  => 'Operación Exitosa!',
                'fecha' => $fecha,
                'info' => $info,
                'type' => 'success'
            ],200);
        }
    }
}
?>

