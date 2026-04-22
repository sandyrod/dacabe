let set_active_sidebar_menu = (element, sub_element) => {

	$("ul.nav-sidebar li").each(function() {
        if ($(this).text().indexOf(element) > 0 || $(this).text().indexOf(sub_element) > 0){
            $(this).children().addClass('active');
        }else
            $(this).children().removeClass('active');
    });
};


let set_select2 = () => {
	$('.select2').select2();

	$('.select2bs4').select2({
	  theme: 'bootstrap4'
	})
};


jQuery(function($) {
	const URL = 'get-tasa-bcv';
    const URLBCV = 'tasa-bcv/saved';
    const TOKEN = '{{csrf_token()}}';

    const getSavedRate = () => {
        /*
        $.ajax({
            url : URLBCV, type : 'get', data: { _token : TOKEN }, dataType : 'json',
            success : function(response) {
                let rate = '';
                if (response.data) {
                    let dollarLocale = Intl.NumberFormat('es-VE');
                    rate = '<div class="btn btn btn-info bcv-rate"> BCV: <b> Bs. ' + dollarLocale.format(response.data.rate) + '</b></div>';
                }
                $('.bcv-rate').html(rate);
            },
            error : function(xhr, status) {
                console.log('error: ', xhr, status);            
                $('.bcv-rate').html('');
            },
            complete:function(response){}
        });
        */
    };

    $.ajax({
        url : URL, type : 'get', data: { _token : TOKEN }, dataType : 'json',
        success : function(response) {
            //if (response.data && (response.data.user.email == 'dacabe@gmail.com' || response.data.user.email == 'vendedordacabe@gmail.com')){
            if (response.data){
            	let rate = '';
            	if (response.data) {
            		let dollarLocale = Intl.NumberFormat('es-VE');
            		rate = '<div class="btn btn btn-info bcv-rate"> <b> ' + dollarLocale.format(response.data.rate) + '</b></div>';
            	}
                $('.bcv-rate').html(rate);
            }
        },
        error : function(xhr, status) {
            $('.bcv-rate').html('');
            //getSavedRate();
        },
        complete:function(response){}
    });
    
})