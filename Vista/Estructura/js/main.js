// main.js: manejo mínimo de UI para productos/carrito y navegación por rol
document.addEventListener('DOMContentLoaded', function(){
  // cargar productos de ejemplo (temporal)
  const productos = [
    {id:1, nombre:'Adorno Navidad - Estrella', precio:1200},
    {id:2, nombre:'Arbolito 30cm', precio:2500},
    {id:3, nombre:'Luces LED', precio:800},
  ];

  const cont = document.getElementById('productos');
  if(cont){
    productos.forEach(p=>{
      const col = document.createElement('div'); col.className='col-12 col-md-4 mb-3';
      col.innerHTML = `
        <div class="card h-100">
          <img src="/Vista/Estructura/img/placeholder.png" class="card-img-top" alt="${p.nombre}">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title">${p.nombre}</h5>
            <p class="card-text">Precio: $${p.precio}</p>
            <div class="mt-auto">
              <button class="btn btn-sm btn-primary add-to-cart" data-id="${p.id}" data-nombre="${p.nombre}" data-precio="${p.precio}">Agregar</button>
            </div>
          </div>
        </div>
      `;
      cont.appendChild(col);
    });
  }

  // carrito en memoria (temporal)
  const carrito = [];
  document.addEventListener('click', function(e){
    if(e.target && e.target.classList.contains('add-to-cart')){
      const id = e.target.dataset.id, nombre = e.target.dataset.nombre, precio = Number(e.target.dataset.precio);
      carrito.push({id,nombre,precio});
      renderCarrito();
    }
  });

  function renderCarrito(){
    const c = document.getElementById('carrito');
    if(!c) return;
    if(carrito.length===0){ c.innerHTML = '<p>El carrito está vacío.</p>'; return; }
    let html = '<ul class="list-group">';
    carrito.forEach((it,i)=> html += `<li class="list-group-item d-flex justify-content-between">${it.nombre} <span>$${it.precio}</span></li>`);
    html += '</ul>';
    c.innerHTML = html;
  }

});
