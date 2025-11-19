<?php
// Vista/Estructura/header.php
// Header común con Bootstrap navbar
?>
<!doctype html>
<html lang="es">
<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>ChrismasMarket</title>
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
		<link href="/Vista/Estructura/css/styles.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
	<div class="container-fluid">
		<a class="navbar-brand" href="/Vista/index.php">ChrismasMarket</a>
		<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="navbarNav">
			<ul class="navbar-nav me-auto mb-2 mb-lg-0">
				<li class="nav-item"><a class="nav-link" href="/Vista/producto/listado.php">Productos</a></li>
				<li class="nav-item"><a class="nav-link" href="/Vista/compra/listado.php">Compras</a></li>
			</ul>
			<ul class="navbar-nav">
				<li class="nav-item"><a class="nav-link" href="/Vista/login.php">Login</a></li>
			</ul>
		</div>
	</div>
</nav>

