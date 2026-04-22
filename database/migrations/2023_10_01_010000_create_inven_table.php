<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateInvenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::dropIfExists('inven');
        Schema::create('inven', function (Blueprint $table) {
            $table->increments('id');
            $table->string('codigo', 30); 
            $table->string('descr', 200); 
            $table->string('aplic1', 200)->nullable(); 
            $table->string('alterno', 30)->nullable(); 
            $table->string('controla', 30)->nullable(); 
            $table->string('tipo', 30)->default('Simple');
            $table->string('cunimedd', 30)->nullable(); 
            $table->string('dunimedd', 200)->nullable();
            $table->string('cunimedm', 30)->nullable(); 
            $table->string('dunimedm', 200)->nullable();
            $table->integer('unidademp')->default(1);
            $table->integer('unipaq')->nullable();
            $table->integer('peso')->nullable();
            $table->string('cgrupo', 30)->nullable(); 
            $table->string('csgrupo', 30)->nullable();
            $table->string('cdpto', 30)->nullable();
            $table->string('cubica', 30)->nullable();
            $table->string('ctipprod', 30)->nullable();
            $table->string('cimpuest', 30)->nullable();
            $table->float('impuest', 9, 2)->nullable();
            $table->float('monto', 14, 3)->nullable();
            $table->integer('aplicaisv')->nullable();
            $table->integer('aplicdes')->nullable();
            $table->integer('balanza')->nullable();
            $table->integer('leedcto')->nullable();
            $table->integer('leeprec')->nullable();
            $table->integer('leecant')->nullable();
            $table->integer('rebunidc')->nullable();
            $table->integer('activo')->nullable();
            $table->string('cdescue', 30)->nullable();
            $table->float('descue', 9, 2)->nullable();
            $table->float('mdescue', 14, 3)->nullable();
            $table->string('foto', 100)->nullable();
            $table->string('tcosto', 10)->nullable();
            $table->float('ultimo', 19, 6)->nullable();
            $table->float('actual', 14, 3)->nullable();
            $table->float('promedio', 14, 3)->nullable();
            $table->float('ulttdolar', 14, 3)->nullable();
            $table->float('actudolar', 14, 3)->nullable();
            $table->float('actualdl', 14, 3)->nullable();
            $table->float('precio1', 14, 3)->nullable();
            $table->float('precio2', 14, 3)->nullable();
            $table->float('precio3', 14, 3)->nullable();
            $table->float('precio4', 14, 3)->nullable();
            $table->float('precio5', 14, 3)->nullable();
            $table->float('base1', 14, 3)->nullable();
            $table->float('base2', 14, 3)->nullable();
            $table->float('base3', 14, 3)->nullable();
            $table->float('base4', 14, 3)->nullable();
            $table->float('base5', 14, 3)->nullable();
            $table->float('porc1', 14, 3)->nullable();
            $table->float('porc2', 14, 3)->nullable();
            $table->float('porc3', 14, 3)->nullable();
            $table->float('porc4', 14, 3)->nullable();
            $table->float('porc5', 14, 3)->nullable();
            $table->float('smin', 14, 3)->nullable();
            $table->float('smax', 14, 3)->nullable();
            $table->float('actunidad', 14, 3)->nullable();
            $table->float('impadic', 14, 3)->nullable();
            $table->string('impdes', 20)->nullable();
            $table->float('impmonto', 14, 3)->nullable();
            $table->float('cantidad', 14, 3)->nullable();
            $table->integer('leeofer')->nullable();
            $table->integer('selpre')->nullable();
            $table->integer('leeser')->nullable();
            $table->string('redond1', 1)->nullable();
            $table->string('redond2', 1)->nullable();
            $table->string('redond3', 1)->nullable();
            $table->string('redond4', 1)->nullable();
            $table->string('redond5', 1)->nullable();
            $table->float('iva1', 14, 3)->nullable();
            $table->float('iva2', 14, 3)->nullable();
            $table->float('iva3', 14, 3)->nullable();
            $table->float('iva4', 14, 3)->nullable();
            $table->float('iva5', 14, 3)->nullable();
            $table->float('pvpm1', 14, 3)->nullable();
            $table->float('pvpm2', 14, 3)->nullable();
            $table->float('pvpm3', 14, 3)->nullable();
            $table->float('pvpm4', 14, 3)->nullable();
            $table->float('pvpm5', 14, 3)->nullable();
            $table->float('cantmin', 5, 0)->nullable();
            $table->integer('leetit')->nullable();
            $table->integer('leesecc')->nullable();
            $table->integer('leerec')->nullable();
            $table->integer('arttipnot')->nullable();
            $table->integer('aplicacert')->nullable();
            $table->string('nrodocu', 10)->nullable();
            $table->string('nommaqfis', 50)->nullable();
            $table->float('montofina', 14, 3)->nullable();
            $table->date('ultven')->nullable();
            $table->date('ultcom')->nullable();
            $table->float('ultcanven', 14, 3)->nullable();
            $table->float('acucanven', 14, 3)->nullable();
            $table->float('ultcancom', 14, 3)->nullable();
            $table->float('acucancom', 14, 3)->nullable();
            $table->string('cambio', 1)->nullable();
            $table->date('fecavicam')->nullable();
            $table->float('costoad', 14, 3)->nullable();
            $table->date('fecvenc')->nullable();
            $table->integer('prodreg')->nullable();
            $table->integer('prodimp')->nullable();
            $table->string('tasa', 1)->nullable();
            $table->integer('maxpreg')->nullable();
            $table->string('ctalla', 3)->nullable();
            $table->string('cmcolor', 3)->nullable();
            $table->string('cmodelo', 6)->nullable();
            $table->integer('notasa')->nullable();
            $table->string('codubic', 30)->nullable(); 
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
        Schema::dropIfExists('inven');
    }
}
