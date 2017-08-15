    <div class="panel panel-default">
        <!-- Default panel contents -->
        <div class="panel-heading">Calculadora Financiera</div>
        <div class="panel-body">

            <form class="form-horizontal" id="calc_form">
                <div class="form-group">
                    <label for="calc_monto" class="col-sm-2 control-label">Monto</label>
                    <div class="col-sm-10">
                    <input type="number" class="form-control" id="calc_monto" placeholder="Monto" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="calc_tiempo" class="col-sm-2 control-label">Tiempo</label>
                    <div class="col-sm-10">
                    <input type="number" min="1"  mqx="15" step="1" class="form-control" id="calc_tiempo" placeholder="Tiempo" required>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <button type="submit" class="btn btn-info" id="calc_calcular">Calcular</button>
                        <img src="<?php echo esc_url( plugins_url( 'img/loading.gif', __FILE__ ) ) ?>" alt="cargando..." style="display:none; height: 40px;" class="loader"  />
                    </div>
                </div>
            </form>

            <div class="resultados" style="display:none;">
                <div class="alert alert-info" role="alert">
                    Recibo <span id='calc_cantidad' > 0.00 </span> acciones, precio  <span id='calc_costo_accion' > 0.00 </span>
                </div>
            </div>
        </div>

        <!-- Table -->
        <table id="calc_tabla" class="resultados table table-striped" style="display:none;">
            <thead>
                <tr>
                    <th>Año</th>
                    <th>Multiplicador Dividendo</th>
                    <th>Dinero</th>
                </tr>
            </thead>
            <tbody>        
            </tbody>
        </table>
    </div>

<?php 
    echo '<script type="text/javascript"> var ajaxurl = "' . admin_url('admin-ajax.php') . '";   </script>';
?>

<script>

    (function($) {

        $(document).ready(function(){

            $('#calc_form').on('submit', function(e){

                e.preventDefault();

                $('.resultados').fadeOut();
                $('.loader').fadeIn();
                
                var monto = $('#calc_monto').val();
                var tiempo = $('#calc_tiempo').val();

                var data = {
                    'action': 'get_dato_anual',
                    'tiempo': tiempo
                };

                $.post(ajaxurl, data, function(response) {

                    if(response.exitoso){

                        var d = new Date();
                        var n = d.getMonth() + 1;
                        var promedio_mes = response.valores[ 'mes_' + n ];

                        var acciones =  Math.floor( monto / promedio_mes );
                        $('#calc_costo_accion').html(parseFloat(promedio_mes).toLocaleString('en-US', { style: 'currency', currency: 'USD' }));
                        $('#calc_cantidad').html(acciones);
                        var tabla = "";
                        var dividendo = 0;
                        var monto_ano = 0;
                        var total = 0;
                        for( i= response.ano_buscar; i <= response.ano_actual; i++ ){
                            dividendo = response.dividendos[i];
                            monto_ano = acciones * dividendo;
                            total += monto_ano;
                            tabla += "<tr> <td>"+i+"</td> <td>"+dividendo+"</td> <td>"+monto_ano.toLocaleString('en-US', { style: 'currency', currency: 'USD' })+"</td> </tr>";
                        }

                        tabla += "<tr> <td colspan='2'> Total </td> <td>"+total.toLocaleString('en-US', { style: 'currency', currency: 'USD' }) +"</td> </tr>";

                        $('#calc_tabla tbody').html(tabla);
                        $('.resultados').fadeIn();
                        $('.loader').fadeOut();
                    }else{
                        alert(response.mensaje);
                        $('.loader').fadeOut();
                    }
                });
            });

        });
        
    })( jQuery );

</script>