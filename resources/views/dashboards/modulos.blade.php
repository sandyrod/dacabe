@php ($modules = (new App\Models\CompanyModule)->with('module')->where('expired_at', '>=', \Carbon\Carbon::today()->toDateString())->where('company_id', auth()->user()->company_id)->get()->take(20))
@if ($modules && sizeof($modules))
    <div class="row">
      <div class="col-md-12">
        <div class="card card-primary card-outline">
          
              <div class="card-header border-transparent">
                <h3 class="card-title text-primary"><i class="fa fa-arrow-circle-right"></i> <b> Módulos Activos</b></h3>

                <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                  <button type="button" class="btn btn-tool" data-card-widget="remove">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
              <!-- /.card-header -->
              <div class="card-body p-0">
                
                @foreach(@$modules as $item)
                  @if (@$item->module->url)
                    <a href="{{ $item->module->url }}" class="btn btn-app11 btn-sdmodule">
                      <i class="{{ $item->module->icon }}"></i><br> {{ $item->module->button_text }}
                    </a>
                  @endif
                @endforeach


                <!-- /.table-responsive -->
              </div>
              <!-- /.card-body -->
              <div class="card-footer clearfix">
                
              </div>
              <!-- /.card-footer -->
            
        
      </div>
      <!--
      <div class="row mb-3">
        <div class="col-md-6 offset-md-3">
          <a href="#" class="btn btn-lg btn-outline-primary btn-block">Ver Todas las facturas</a>
        </div>
      </div>
  -->
    </div>

    
    </div>
@endif