(function ($) {

	var data = JSON.parse($("#store-data").html());

  table = $('#store-table').DataTable({
			ordering: true,
			searching: true,
			//lengthChange: false,
			lengthMenu: [10, 25, 50],
			pageLength: 10,
			columns: [
        {'title': 'Name', 'data': 'name'},
				{'title': 'Brand', 'data': 'brand'},
				{'title': 'Address', 'data': 'address',
				'render':
					function (data, type, row) {
						return row.address + ' ' + row.city + ', ' + row.state + '. ' + row.zip;
					},
				},
				{'title': 'Phone', 'data': 'phone' },
		  ],
			 data: data
	 });


}(jQuery));
