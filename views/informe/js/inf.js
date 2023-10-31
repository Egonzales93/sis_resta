$(function() {
	$('#navbar-c').addClass("white-bg");
    $('#informes').addClass("active");
});

$(".ventas").click(function() {
	$('#list_a').empty();
	$('#list_a').append(
		$('<div class="panel panel-default panel-shadow animated flipInX"/>')
			.append(
			$('<div class="panel-body no-padding"/>')
			.append(
				$('<div class="list-group"/>')
				.append('<a class="list-group-item link font-14" href="informe/venta_all">Todas las ventas</a>')
				.append('<a class="list-group-item link font-14" href="informe/venta_del">Ventas por delivery</a>')
				.append('<a class="list-group-item link font-14" href="informe/venta_prod">Productos vendidos por canal de venta <span class="badge badge-danger">nuevo</span></a>')
				.append('<a class="list-group-item link font-14" href="informe/venta_prod_kardex">Kardex de productos por ventas <span class="badge badge-danger">nuevo</span></a>')
				.append('<a class="list-group-item link font-14" href="informe/venta_prod_margen">Margen de ganancia por productos vendidos <span class="badge badge-danger">nuevo</span></a>')
				.append('<a class="list-group-item link font-14" href="informe/venta_mozo">Ventas por mesero</a>')
				.append('<a class="list-group-item link font-14" href="informe/venta_fpago">Formas de pago</a>')
				.append('<a class="list-group-item link font-14" href="informe/venta_desc">Descuentos</a>')
				.append('<a class="list-group-item link font-14" href="informe/venta_cort">Cortesias <span class="badge badge-danger">nuevo</span></a>')
				)
			)
		)
});

$(".compras").click(function() {
	$('#list_a').empty();
	$('#list_a').append(
		$('<div class="panel panel-default panel-shadow animated flipInX"/>')
			.append(
			$('<div class="panel-body no-padding"/>')
			.append(
				$('<div class="list-group"/>')
				.append('<a class="list-group-item link font-14" href="informe/compra_all">Todas las compras</a>')
				)
			)
		)
});

$(".finanzas").click(function() {
	$('#list_a').empty();
	$('#list_a').append(
		$('<div class="panel panel-default panel-shadow animated flipInX"/>')
			.append(
			$('<div class="panel-body no-padding"/>')
			.append(
				$('<div class="list-group"/>')
				.append('<a class="list-group-item link font-14" href="informe/finanza_arq">Aperturas y cierres</a>')
				.append('<a class="list-group-item link font-14" href="informe/finanza_ing">Todos los ingresos</a>')
				.append('<a class="list-group-item link font-14" href="informe/finanza_egr">Todos los egresos</a>')
				.append('<a class="list-group-item link font-14" href="informe/finanza_rem">Egresos por remuneraci&oacute;n</a>')
				.append('<a class="list-group-item link font-14" href="informe/finanza_adel">Personal</a>')
				)
			)
		)
});

$(".inventario").click(function() {
	$('#list_a').empty();
	$('#list_a').append(
		$('<div class="panel panel-default panel-shadow animated flipInX"/>')
			.append(
			$('<div class="panel-body no-padding"/>')
			.append(
				$('<div class="list-group"/>')
				.append('<a class="list-group-item link font-15" href="informe/inventario_kardex">Kardex</a>')
				)
			)
		)
});


$(".otros").click(function() {
	$('#list_a').empty();
	$('#list_a').append(
		$('<div class="panel panel-default panel-shadow animated flipInX"/>')
			.append(
			$('<div class="panel-body no-padding"/>')
			.append(
				$('<div class="list-group"/>')
				.append('<a class="list-group-item link font-14" href="informe/oper_anul">Anulaciones de pedidos</a>')
				)
			)
		)
});


