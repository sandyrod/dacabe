<div class="modal fade" id="modal_sellers">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title"><span id="seller_zone"></span> </h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">

				<div class="row">
					<div class="col-md-4">
						<div class="info-box">
				  			<span class="info-box-icon bg-info"><i class="fa fa-calculator"></i></span>

							<div class="info-box-content">
								<span class="info-box-text">Saldo</span>
								<span class="info-box-number" id="seller_balance"></span>
							</div>
							<!-- /.info-box-content -->
							</div>
					</div>
					<div class="col-md-4">
						<div class="info-box">
				  			<span class="info-box-icon bg-danger"><i class="fa fa-shopping-cart"></i></span>

							<div class="info-box-content">
								<span class="info-box-text">Ventas</span>
								<span class="info-box-number" id="seller_orders"></span>
							</div>
							<!-- /.info-box-content -->
							</div>
					</div>
					<div class="col-md-4">
						<div class="info-box">
							<span class="info-box-icon bg-success"><i class="fa fa-money-bill"></i></span>

							<div class="info-box-content">
								<span class="info-box-text">Pagos</span>
								<span class="info-box-number" id="seller_payments"></span>
							</div>
							<!-- /.info-box-content -->
							</div>
					</div>
				</div>
				
				<div class="row">
					<div class="col-md-12">
						<a href="#" class="btn btn-primary btn-block form-group" onclick="event.preventDefault(); printSellerBalance();"><i class="fa fa-print"></i> Imprimir Balance</a>
					</div>
				</div>

				<div id="accordion">
					<div class="card card-info">
						<div class="card-header p-0 border-bottom-0">
							<ul class="nav nav-tabs" id="sellerTab" role="tablist" style="width: 100%;">
								<li class="nav-item" style="flex:1;">
									<a class="nav-link active text-center" id="add-payment-tab" data-toggle="tab" href="#add-payment" role="tab" aria-controls="add-payment" aria-selected="true">
										<i class="fa fa-dollar-sign"></i> Agregar Pago
									</a>
								</li>
								<li class="nav-item" style="flex:1;">
									<a class="nav-link text-center" id="payments-list-tab" data-toggle="tab" href="#payments-list" role="tab" aria-controls="payments-list" aria-selected="false">
										<i class="fa fa-list"></i> Pagos Realizados
									</a>
								</li>
							</ul>
						</div>
						<div class="card-body">
							<div class="tab-content" id="sellerTabContent">
								<div class="tab-pane fade show active" id="add-payment" role="tabpanel" aria-labelledby="add-payment-tab">
									<div class="card card-info mb-0">
										<div class="card-body">
											<div class="row">
												<div class="col-md-3">
													<div class="form-group">
														{{ Form::label('modal_fecha_pago', 'Fecha del pago', ['class' => 'control-label']) }}
														{!! Form::date('modal_fecha_pago', now(), ['class' => 'form-control ', 'placeholder' => 'fecha_pago', 'id' => 'modal_fecha_pago']) !!}
														<input type="hidden" id="modal_seller_id" />
													</div>
												</div>
												<div class="col-md-3">
													<div class="form-group">
														{{ Form::label('modal_monto_pago', 'Monto', ['class' => 'control-label']) }}
														{!! Form::number('modal_monto_pago', 0, ['class' => 'form-control ', 'placeholder' => 'Monto', 'id' => 'modal_monto_pago']) !!}
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														{{ Form::label('modal_descripcion_pago', 'Descripción', ['class' => 'control-label']) }}
														{!! Form::text('modal_descripcion_pago', null, ['class' => 'form-control ', 'placeholder' => 'Detalle del pago', 'id' => 'modal_descripcion_pago']) !!}
													</div>
												</div>
											</div>
											<div class="col-12">
												<span id="payment_order_list"></span>
											</div>
											<div class="row">
												<div class="col-12">
													<button class="btn btn-primary btn-block" onclick="savePayment()" id="btn_payment"><i class="fa fa-check"></i> Registrar pago</button>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="tab-pane fade" id="payments-list" role="tabpanel" aria-labelledby="payments-list-tab">
									<div class="card card-info mb-0">
										<div class="card-body">
											<div class="row">
												<div class="col-12 mt-2">
													<ul class="products-list product-list-in-card" style="max-height: 300px; overflow-y: scroll;">
														<span id="li_payments"></span>
													</ul>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				
			</div>	    	
			
		</div>	  
	</div>	
</div>
