$(function()
{
	//gestionnaires d'événements
	$('.admin-categories [data-action="update"]').on('click', updateCategory);
	$('.admin-products [data-action="update"]').on('click', updateProduct);
	$('.admin-customers [data-action="update"]').on('click', updateCustomer);
	$('.admin-orders [data-action="update"]').on('click', updateOrder);
	$('.admin-orderlines [data-action="update"]').on('click', updateOrderLine);
});

//mise à jour d'une catégorie
function updateCategory() {
	//récupération des éléments HTML
	var $button = $(this);
	var $tr = $button.parent().parent();

	//récupération des infos PHP
	var CSRFToken = $button.data('csrftoken');
	var id = $button.data('id');
	
	//récupération des informations saisies
	var name = $tr.children('td:nth-child(2)').text();

	//récupération de l'adresse de la racine du site
	var root = $('base').attr('href');

	//requête XHR
	$.ajax({
		method:'post',
		url:root+'categories/updateOne',
		data: 
		{
			id: id, 
			name: name,
			CSRFToken: CSRFToken
		},
		success: function()
		{
			alert('La catégorie a bien été modifiée.');
		}
	});
}

//mise à jour d'un produit
function updateProduct() {
	//récupération des éléments HTML
	var $button = $(this);
	var $tr = $button.parent().parent();

	//récupération des infos PHP
	var CSRFToken = $button.data('csrftoken');
	var id = $button.data('id');
	
	//récupération des informations saisies
	var name = $tr.children('td:nth-child(2)').text();
	var description = $tr.children('td:nth-child(3)').text();
	var imagePath = $tr.children('td:nth-child(4)').text();
	var priceHT = $tr.children('td:nth-child(5)').text().replace(',', '.');
	var VATRate = $tr.children('td:nth-child(6)').text().replace(',', '.');
	var idCategory = parseInt($tr.children('td:nth-child(8)').children('select').children('option:selected').val());

	//récupération de l'adresse de la racine du site
	var root = $('base').attr('href');

	//requête XHR
	$.ajax({
		method:'post',
		url:root+'products/update',
		data: 
		{
			id: id, 
			name: name,
			description: description,
			imagePath: imagePath,
			priceHT: priceHT,
			VATRate: VATRate,
			idCategory: idCategory,
			CSRFToken: CSRFToken
		},
		success: function()
		{
			alert('Le produit a bien été modifié.');
		}
	});
}

//mise à jour d'un client
function updateCustomer() {

	//récupération des éléments HTML
	var $button = $(this);
	var $tr = $button.parent().parent();

	//récupération des infos PHP
	var CSRFToken = $button.data('csrftoken');
	var id = $button.data('id');
	
	//récupération des informations saisies
	var email = $tr.children('td:nth-child(2)').text();
	var civility = $tr.children('td:nth-child(3)').text();
	var firstName = $tr.children('td:nth-child(4)').text();
	var lastName = $tr.children('td:nth-child(5)').text();
	var address = $tr.children('td:nth-child(6)').text();
	var zipCode = $tr.children('td:nth-child(7)').text();
	var city = $tr.children('td:nth-child(8)').text();
	var country = $tr.children('td:nth-child(9)').text();
	var phoneNumber = $tr.children('td:nth-child(10)').text();

	//récupération de l'adresse de la racine du site
	var root = $('base').attr('href');

	//requête XHR
	$.ajax({
		method:'post',
		url:root+'customers/adminUpdate',
		data: 
		{
			email: email,
			civility: civility,
			firstName: firstName,
			lastName: lastName,
			address: address,
			zipCode: zipCode,
			city: city,
			country: country,
			phoneNumber: phoneNumber,
			id: id,
			CSRFToken: CSRFToken
		},
		success: function()
		{
			alert('Le client a bien été modifié.');
		}
	});
}

//mise à jour d'une commande
function updateOrder() {
	//récupération des éléments HTML
	var $button = $(this);
	var $tr = $button.parent().parent();

	//récupération des infos PHP
	var CSRFToken = $button.data('csrftoken');
	var id = $button.data('id');
	
	//récupération des informations saisies
	var purchaseDate = $tr.children('td:nth-child(2)').text();
	var billingCivility = $tr.children('td:nth-child(3)').text();
	var billingFirstName = $tr.children('td:nth-child(4)').text();
	var billingLastName = $tr.children('td:nth-child(5)').text();
	var billingAddress = $tr.children('td:nth-child(6)').text();
	var billingZipCode = $tr.children('td:nth-child(7)').text();
	var billingCity = $tr.children('td:nth-child(8)').text();
	var billingCountry = $tr.children('td:nth-child(9)').text();
	var billingPhoneNumber = $tr.children('td:nth-child(10)').text();
	var deliveryCivility = $tr.children('td:nth-child(11)').text();
	var deliveryFirstName = $tr.children('td:nth-child(12)').text();
	var deliveryLastName = $tr.children('td:nth-child(13)').text();
	var deliveryAddress = $tr.children('td:nth-child(14)').text();
	var deliveryZipCode = $tr.children('td:nth-child(15)').text();
	var deliveryCity = $tr.children('td:nth-child(16)').text();
	var deliveryCountry = $tr.children('td:nth-child(17)').text();
	var deliveryPhoneNumber = $tr.children('td:nth-child(18)').text();
	var idCustomer = $tr.children('td:nth-child(19)').text();

	//récupération de l'adresse de la racine du site
	var root = $('base').attr('href');

	//requête XHR
	$.ajax({
		method:'post',
		url:root+'orders/update',
		data: 
		{
			purchaseDate: purchaseDate,
			billingCivility: billingCivility,
			billingFirstName: billingFirstName,
			billingLastName: billingLastName,
			billingAddress: billingAddress,
			billingZipCode: billingZipCode,
			billingCity: billingCity,
			billingCountry: billingCountry,
			billingPhoneNumber: billingPhoneNumber,
			deliveryCivility: deliveryCivility,
			deliveryFirstName: deliveryFirstName,
			deliveryLastName: deliveryLastName,
			deliveryAddress: deliveryAddress,
			deliveryZipCode: deliveryZipCode,
			deliveryCity: deliveryCity,
			deliveryCountry: deliveryCountry,
			deliveryPhoneNumber: deliveryPhoneNumber,
			idCustomer: idCustomer,
			id: id,
			CSRFToken: CSRFToken
		},
		success: function()
		{
			alert('La commande a bien été modifiée.');
		}
	});
}

function updateOrderLine() {
	//récupération des éléments HTML
	var $button = $(this);
	var $tr = $button.parent().parent();

	//récupération des infos PHP
	var CSRFToken = $button.data('csrftoken');
	var id = $button.data('id');
	var idOrder = $button.data('orderid');

	//récupération des informations saisies
	var idProduct = parseInt($tr.children('td:nth-child(1)').children('select').children('option:selected').val());
	var nameProduct = $tr.children('td:nth-child(1)').children('select').children('option:selected').text();
	var priceHTProduct = parseFloat($tr.children('td:nth-child(2)').text().replace(',', '.'));	//remplacement éventuel de la virgule par un point
	var quantity = parseInt($tr.children('td:nth-child(5)').text().slice(2)); //supression du 'x ' initial
	var VATRateProduct = parseFloat($tr.children('td:nth-child(4)').text().replace(',', '.')); //remplacement éventuel de la virgule par un point

	//récupération de l'adresse de la racine du site
	var root = $('base').attr('href');
	
	//si l'id, le prix, la quantité et le taux de TVA récupérés sont bien numériques
	if (!isNaN(idProduct) && !isNaN(priceHTProduct) && !isNaN(quantity) && !isNaN(VATRateProduct)) {
		
		//requête XHR
		$.ajax({
				method:'post',
				url:root+'orderlines/update',
				data: 
				{
					idProduct: idProduct,
					idOrder: idOrder,
					nameProduct: nameProduct,
					priceHTProduct: priceHTProduct,
					quantity: quantity,
					VATRateProduct: VATRateProduct,
					CSRFToken: CSRFToken,
					id: id
				},
				success: function()
				{
					alert('Le produit a bien été modifié.');
				}
			});
	}
	else {
		alert('Veuillez vérifier votre saisie.');
	}
}
