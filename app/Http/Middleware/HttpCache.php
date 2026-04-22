<?php

namespace App\Http\Middleware;

use Closure;
use Cache;

class HttpCache
{
	public function handle($request, Closure $next, $time = 60)
   {
      // Obtenemos la url de la petición y generamos con ella una
      // clave única para guardar en cache.

      $url = $request->fullUrl();
      $key = md5($url);

      // Si la clave existe en caché, hay que obtener el contenido,
      // devolverlo como respuesta y detener el resto de la aplicación.

      if(Cache::has($key)){
           $content = Cache::get($key);
           return response($content);
      }

      // De lo contrario, se continua con el resto de la aplicación...

      $response = $next($request);

      // ...y se guarda el contenido de la respuesta durante el tiempo
      // solicitado en caché bajo la clave única generada más arriba. 

      Cache::put($key, $response->getContent(), (float)$time);

      return $response;

   }
}
