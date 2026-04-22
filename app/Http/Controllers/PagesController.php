<?php
    namespace App\Http\Controllers;

    use App\Models\{Noticia, Landing};
    use Illuminate\Http\Request;
    use DB;
    
    class PagesController extends Controller
    {

        public function getLandingMenu(Request $request, $landing='')
        {
            $groups = [];
            $groups[] = [ 'filter'=>'*', 'name'=>'TODO'];
            $groups[] = [ 'filter'=>'.filter-burger', 'name'=>'BURGER'];
            $groups[] = [ 'filter'=>'.filter-hotdog', 'name'=>'HOTDOG'];
            $groups[] = [ 'filter'=>'.filter-kidbox', 'name'=>'KIDBOX'];
            $groups[] = [ 'filter'=>'.filter-paninis', 'name'=>'PANINIS'];
            $groups[] = [ 'filter'=>'.filter-additionals', 'name'=>'ADICIONALES'];

            $menu = $this->getMenu();

            $landing = (new Landing)->where('slug', $landing)->first();

            return view('landing.pages.menues', compact(['menu', 'groups', 'landing']));
        }

        public function getFabios(Request $request, $goto='')
        {
            $groups = [];
            $groups[] = [ 'filter'=>'*', 'name'=>'TODO'];
            $groups[] = [ 'filter'=>'.filter-burger', 'name'=>'BURGER'];
            $groups[] = [ 'filter'=>'.filter-hotdog', 'name'=>'HOTDOG'];
            $groups[] = [ 'filter'=>'.filter-kidbox', 'name'=>'KIDBOX'];
            $groups[] = [ 'filter'=>'.filter-paninis', 'name'=>'PANINIS'];
            $groups[] = [ 'filter'=>'.filter-additionals', 'name'=>'ADICIONALES'];

            $menu = $this->getMenu();
            return view('landing.pages.fabios', compact(['goto', 'menu', 'groups']));
        }

        private function getMenu()
        {
            $id = 1;
            $menu = [];
            $menu[] = [ 
                'id'=>$id++, 
                'filter'=>'filter-burger', 
                'name'=>'Personal Burger S/Papas', 
                'description'=>'100 gr de carne, queso americano, lechuga, tomate, cebolla, salsa de la casa y ketchup', 
                'price'=>'3.90', 
                'img'=>'burger.jpg' 
            ];

            $menu[] = [ 
                'id'=>$id++, 
                'filter'=>'filter-burger', 
                'name'=>'Basic Carne', 
                'description'=>'130 gramos de carne, queso americano, salsa de la casa, ketchup.', 
                'price'=>'4.50', 
                'img'=>'burger.jpg' 
            ];

            $menu[] = [ 
                'id'=>$id++, 
                'filter'=>'filter-burger', 
                'name'=>'Basic Pollo', 
                'description'=>'130 gramos de carne, queso americano, salsa de la casa, ketchup.', 
                'price'=>'5.50', 
                'img'=>'burger.jpg' 
            ];

            $menu[] = [ 
                'id'=>$id++, 
                'filter'=>'filter-burger', 
                'name'=>'Classic', 
                'description'=>'130 gramos de carne, queso americano, tomate, lechuga y cebolla, salsa de la casa, ketchup.', 
                'price'=>'5.50', 
                'img'=>'burger.jpg' 
            ];
    
            $menu[] = [ 
                'id'=>$id++, 
                'filter'=>'filter-burger', 
                'name'=>'American Bacon Carne', 
                'description'=>'150 gramos de carne o pollo, queso americano, salsa de la casa, cebolla, pepinillo, salsa ketchup y tocineta.', 
                'price'=>'5.99', 
                'img'=>'americana.jpg' 
            ];

            $menu[] = [ 
                'id'=>$id++, 
                'filter'=>'filter-burger', 
                'name'=>'American Bacon Pollo', 
                'description'=>'150 gramos de carne o pollo, queso americano, salsa de la casa, cebolla, pepinillo, salsa ketchup y tocineta.', 
                'price'=>'6.50', 
                'img'=>'americana_pollo.jpg' 
            ];

            $menu[] = [ 
                'id'=>$id++, 
                'filter'=>'filter-burger', 
                'name'=>'Granjera Crispy', 
                'description'=>'150 gramos de pollo crispy, queso americano, lechuga, salsa de la casa, mayonesa, cebolla, pepinillo, tocineta.', 
                'price'=>'6.99', 
                'img'=>'crispy.jpg' 
            ];

            $menu[] = [ 
                'id'=>$id++, 
                'filter'=>'filter-burger', 
                'name'=>'Cesar Crispy', 
                'description'=>'150 gramos de pollo crispy o a la plancha, lechuga, aderezo cesar, tocineta, queso pecorino.', 
                'price'=>'6.99', 
                'img'=>'crispy.jpg' 
            ];


            $menu[] = [ 
                'id'=>$id++, 
                'filter'=>'filter-burger', 
                'name'=>'Super CheeseBurger', 
                'description'=>'150 gramos de carne, queso mozarella, queso americano, cebolla caramelizada, pepinillo, tocineta, salsa de la casa y ketchup, papas fritas dentro de la hamburguesa.', 
                'price'=>'7.99', 
                'img'=>'cheese_burger.jpg' 
            ];

            $menu[] = [ 
                'id'=>$id++, 
                'filter'=>'filter-hotdog', 
                'name'=>'Clasico', 
                'description'=>'Salchicha alimex, cebolla, salsa de la casa, ketchup, papitas, queso pecorino.', 
                'price'=>'2.50', 
                'img'=>'hotdog.jpg' 
            ];

            $menu[] = [ 
                'id'=>$id++, 
                'filter'=>'filter-hotdog', 
                'name'=>'Cheese Bacon', 
                'description'=>'Salchicha alimex, cebolla, salsa de la casa, ketchup, papitas, queso pecorino, queso amarillo y tocineta.', 
                'price'=>'3.50', 
                'img'=>'hotdog.jpg' 
            ];

            $menu[] = [ 
                'id'=>$id++, 
                'filter'=>'filter-hotdog', 
                'name'=>'Adicional de Papas', 
                'description'=>'', 
                'price'=>'1.50', 
                'img'=>'papas.jpg' 
            ];

            $menu[] = [ 
                'id'=>$id++, 
                'filter'=>'filter-paninis', 
                'name'=>'Clasico', 
                'description'=>'Jamón ahumado, queso amarillo, lechuga, ruedas de tomate, salsa de la casa.', 
                'price'=>'3.99', 
                'img'=>'panini.jpg' 
            ];

            $menu[] = [ 
                'id'=>$id++, 
                'filter'=>'filter-paninis', 
                'name'=>'Capresa de Pollo', 
                'description'=>'Queso mozarella, ruedas de tomate, mayopesto de la casa, toque de pimienta y aceitunas negras, pollo', 
                'price'=>'6.50', 
                'img'=>'panini.jpg' 
            ];

            $menu[] = [ 
                'id'=>$id++, 
                'filter'=>'filter-paninis', 
                'name'=>'Cesar de pollo', 
                'description'=>'Lechuga, tiras de pollo crispy o a la plancha, aderezo cesar , tocineta y queso pecorino.', 
                'price'=>'6.50', 
                'img'=>'panini.jpg' 
            ];

            $menu[] = [ 
                'id'=>$id++, 
                'filter'=>'filter-kidbox', 
                'name'=>'Kids Burger', 
                'description'=>'Hamburguesa basic, papas, jugo o refresco , galleta, juguete ( a disponibilidad)', 
                'price'=>'7.50', 
                'img'=>'kidbox.jpg' 
            ];

            $menu[] = [ 
                'id'=>$id++, 
                'filter'=>'filter-kidbox', 
                'name'=>'Kids Tender', 
                'description'=>'Tender de pollo, papas. jugo o refresco, galleta, juguete (a disponibilidad)', 
                'price'=>'7.50', 
                'img'=>'kidbox.jpg' 
            ];

            $menu[] = [ 
                'id'=>$id++, 
                'filter'=>'filter-additionals', 
                'name'=>'Vegetales (Tomate, Lechuga)', 
                'description'=>'', 
                'price'=>'0.80', 
                'img'=>'vegetales.jpg' 
            ];

            $menu[] = [ 
                'id'=>$id++, 
                'filter'=>'filter-additionals', 
                'name'=>'Facilista', 
                'description'=>'', 
                'price'=>'0.50', 
                'img'=>'facilista.jpg' 
            ];

            $menu[] = [ 
                'id'=>$id++, 
                'filter'=>'filter-additionals', 
                'name'=>'Huevo', 
                'description'=>'', 
                'price'=>'0.50', 
                'img'=>'huevo.jpg' 
            ];

            return $menu;
        }

        
        public function getAndrea(Request $request, $goto='')
        {
            return view('landing.pages.andrea');
        }

        public function getAndrea2(Request $request, $goto='')
        {
            return view('landing.pages.andrea2');
        }
    
        public function getCrewAnchor()
        {
            return view('landing.pages.crew_anchors');
        }

    }
?>

