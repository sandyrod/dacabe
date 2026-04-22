
<script>
	@section('js_functions')

        let url = '{{url('get-calendar')}}'; 
        let token = '{{csrf_token()}}';
        
        $.ajax({
          url : url, type : 'POST', data: { _token : token, id: id  }, dataType : 'json',
          success : function(response){
              load_calendar(response);
          },
          error : function(xhr, status) {
            console.log('error: ', xhr, status);                    
          },
          complete:function(response){}
      });

      let load_calendar = (response) => {
        let cad='';          

        $('#calendar_header').html(cad);

        $('#calendar_content').html(cad);
      };

      

    @endsection
	

    @section('form_ajax_success')

        if(check.status && check.controller=="config"){
            if(check.type=="success")
                $('body').find('.modalformMailConf').trigger('click');
            swal({title: check.title, text: check.text, type: check.type, html: true});
        }
        if(check.status && check.controller=="marcas"){
            if(check.type=="success")
                $('body').find('.modalformBrandConf').trigger('click');
            swal({title: check.title, text: check.text, type: check.type, html: true});
        }

	@endsection
</script>
