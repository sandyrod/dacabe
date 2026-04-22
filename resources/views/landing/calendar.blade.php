
  <h1 class="h1_calendar">Calendario SENIAT</h1>     
  <div class="symbology text-center">
        <div class="category">
          <span class="item concurrent-icon"><i class="fa fa-bell"></i> <strong>Declaración IVA</strong>   </span>
          <br />
          <span class="item concurrent-icon"><i class="fa fa-circle"></i> <strong>Retención ISLR</strong>   </span>
          <br />
          <span class="item concurrent-icon"><strong>RIF:</strong>   </span>
          <span class="item scripting-icon"> 0,5 </span> |
          <span class="item object-oriented-icon"> 6,9 </span> |
          <span class="item functional-icon"> 3,7 </span>|
          <span class="item multi-paradigm-icon"> 4,8 </span> |
          <span class="item mechanical-icon"> 1,2 </p></span>  
            <!--
            <div class="item imperative"></div>
            <p class="text">Imperative</p>
          -->
          </div>
          <!-- 
          <div class="category">
            <div class="item dynamic"></div>
            <p class="text">Dynamic</p>
            <div class="item concurrent"></div>
            <p class="text">Concurrent</p>
            <div class="item multi-paradigm"></div>
            <p class="text">Multi-paradigm</p>
          </div>
        -->
    </div>

    <br />
  
  <div class="row text-center">
    <div class="col-md-3">
    </div>
    <div class="col-md-3 col-sm-6 col-xs-6">
      <div class="btn-group">
        <button type="button" id="mes" class="btn btn-info">Mes</button>
        <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
          <span class="caret"></span>
          <span class="sr-only">Toggle Dropdown</span>
        </button>
        <ul class="dropdown-menu" role="menu">
          <li><a href="#calendario" onclick="change_month(1);">Enero</a></li>
          <li><a href="#calendario" onclick="change_month(2);">Febrero</a></li>
          <li><a href="#calendario" onclick="change_month(3);">Marzo</a></li>
          <li><a href="#calendario" onclick="change_month(4);">Abril</a></li>
          <li><a href="#calendario" onclick="change_month(5);">Mayo</a></li>
          <li><a href="#calendario" onclick="change_month(6);">Junio</a></li>
          <li><a href="#calendario" onclick="change_month(7);">Julio</a></li>
          <li><a href="#calendario" onclick="change_month(8);">Agosto</a></li>
          <li><a href="#calendario" onclick="change_month(9);">Septiembre</a></li>
          <li><a href="#calendario" onclick="change_month(10);">Octubre</a></li>
          <li><a href="#calendario" onclick="change_month(11);">Noviembre</a></li>
          <li><a href="#calendario" onclick="change_month(12);">Diciembre</a></li>                      
        </ul>
      </div> 
    </div>
    <div class="col-md-3  col-sm-6 col-xs-6">
      <div class="btn-group">
        <button type="button" id="rif" class="btn btn-info">RIF</button>
        <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
          <span class="caret"></span>
          <span class="sr-only">Toggle Dropdown</span>
        </button>
        <ul class="dropdown-menu" role="menu">
          <li><a href="#calendario" onclick="change_rif('');">Todos</a></li>
          <li><a href="#calendario" onclick="change_rif('05');">0, 5</a></li>
          <li><a href="#calendario" onclick="change_rif('69');">6, 9</a></li>
          <li><a href="#calendario" onclick="change_rif('37');">3, 7</a></li>
          <li><a href="#calendario" onclick="change_rif('48');">4, 8</a></li>
          <li><a href="#calendario" onclick="change_rif('12');">1, 2</a></li>          
        </ul>
      </div>
    </div>
  </div>

    <div class="periodic-table" id="calendar_header"> 
      
    </div>

    <div id="calendar_content">
    </div>

      
<br /><br /><br /><br /><br /><br />


