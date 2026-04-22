<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMoviventasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::dropIfExists('moviventas');
        Schema::create('moviventas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('codigo', 30); 
            $table->string('descr', 200); 
            $table->string('descrl', 250)->nullable(); 
            $table->float('cantidad', 10, 3)->nullable(); 
            $table->float('monto', 14, 3)->nullable(); 
            $table->float('impuesto', 14, 4)->nullable();
            $table->float('poriva', 15, 2)->nullable(); 
            $table->float('total', 14, 3)->nullable();
            $table->date('fecha')->nullable(); 
            $table->string('tpago', 1)->nullable();
            $table->string('nro', 10)->nullable();
            $table->string('grupo', 3)->nullable();
            $table->string('modelo', 3)->nullable();
            $table->string('tipprod', 6)->nullable();
            $table->string('coddpto', 6)->nullable();
            $table->string('desdpto', 50)->nullable();
            $table->string('codubic', 6)->nullable();
            $table->string('desubic', 50)->nullable();
            $table->string('unidad', 15)->nullable();
            $table->string('impreso', 1)->nullable();
            $table->string('codimpre', 2)->nullable();
            $table->float('porservi', 5, 2)->nullable();
            $table->float('servicio', 14, 3)->nullable();
            $table->string('emple', 10)->nullable();
            $table->string('nomemple', 30)->nullable();
            $table->integer('espera')->nullable();
            $table->string('tipoitem', 1)->nullable();
            $table->integer('nroitem')->nullable();
            $table->float('cantelim', 10, 3)->nullable();
            $table->float('montod', 14, 3)->nullable();
            $table->float('porcd', 15, 2)->nullable();
            $table->integer('tipod')->nullable();
            $table->string('fechad')->nullable();
            $table->string('coddesc', 3)->nullable();
            $table->string('ddesc', 30)->nullable();
            $table->string('catego', 2)->nullable();
            $table->string('caja', 2)->nullable();
            $table->string('codcli', 15)->nullable();
            $table->string('nombre', 200)->nullable();
            $table->string('dpto', 2)->nullable();
            $table->integer('rp')->nullable();
            $table->float('impuest', 9, 2)->nullable();
            $table->float('montorp', 14, 3)->nullable();
            $table->float('ivarp', 14, 3)->nullable();
            $table->float('porivarp', 5, 2)->nullable();
            $table->float('porserrp', 5, 2)->nullable();
            $table->float('servirp', 14, 3)->nullable();
            $table->integer('devolver')->nullable();
            $table->integer('artenv')->nullable();
            $table->integer('precio')->nullable();
            $table->integer('puntos')->nullable();
            $table->integer('nrocorte')->nullable();
            $table->string('tipocli', 1)->nullable();
            $table->string('unicaja', 1)->nullable();
            $table->integer('unidadc')->nullable();
            $table->string('hora', 11)->nullable();
            $table->float('cantdev', 10, 3)->nullable();
            $table->integer('unipaq')->nullable();
            $table->string('estac', 30)->nullable();
            $table->float('peso', 14, 3)->nullable();
            $table->string('cdepos', 6)->nullable();
            $table->float('cofer', 10, 3)->nullable();
            $table->string('codseri', 30)->nullable();
            $table->float('impadic', 9, 2)->nullable();
            $table->float('impmonto', 14, 3)->nullable();
            $table->string('fechades', 100)->nullable();
            $table->string('fechahast', 100)->nullable();
            $table->string('fecharet', 100)->nullable();
            $table->integer('pagdes')->nullable();
            $table->integer('coldes')->nullable();
            $table->integer('cendes')->nullable();
            $table->integer('colcendes')->nullable();
            $table->string('comprob', 10)->nullable();
            $table->string('comprob2', 10)->nullable();
            $table->float('montoret', 14, 3)->nullable();
            $table->string('ctitulo', 50)->nullable();
            $table->integer('nseccion')->nullable();
            $table->string('ccontrol', 20)->nullable();
            $table->float('montondc', 14, 3)->nullable();
            $table->string('localemp', 12)->nullable();
            $table->string('nref', 10)->nullable();
            $table->string('fechadif', 10)->nullable();
            $table->integer('arttipnot')->nullable();
            $table->integer('aplicacert')->nullable();
            $table->string('nrodocu', 10)->nullable();
            $table->string('nommaqfiscal', 50)->nullable();
            $table->float('montofina', 14, 3)->nullable();
            $table->string('codbarra', 15)->nullable();
            $table->string('codalter', 15)->nullable();
            $table->string('facnrofis', 10)->nullable();
            $table->string('tasa', 1)->nullable();
            $table->integer('prodreg')->nullable();
            $table->integer('prodimp')->nullable();
            $table->string('rutaimp', 200)->nullable();
            $table->float('tasadeldia', 9, 4)->nullable();
            $table->string('tipomoneda', 1)->nullable();
            
            $table->integer('company_id')->unsigned();
            $table->timestamps();
        
            $table->foreign('company_id')->references('id')->on('companies')
                ->onUpdate('cascade')->onDelete('cascade');
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
        Schema::dropIfExists('moviventas');
    }
}
