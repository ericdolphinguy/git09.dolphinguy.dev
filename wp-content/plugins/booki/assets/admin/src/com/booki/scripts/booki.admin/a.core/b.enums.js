(function(){

	Booki.OptionalsBookingMode = {
		'eachBooking' : 0
		, 'eachDay' : 1
	};
	Booki.OptionalsListingMode = {
		'checkboxList' : 0
		, 'radioButtonList' : 1
	};
	Booki.BookingWizardMode = {
		'tabs' : 0
		, 'linear' : 1
	};
/**
		@enum
		@description Represents a form element in a Booki.FormElement object.
	*/
	Booki.ElementType = {
		'textbox' : 0
		, 'textarea' : 1
		, 'dropdownlist' : 2
		, 'listbox' : 3
		, 'checkbox' : 4
		, 'radiobutton' : 5
		, 'h1' : 6
		, 'h2' : 7
		, 'h3' : 8
		, 'h4' : 9
		, 'h5' : 10
		, 'h6' : 11
		, 'plaintext' : 12
		, 'tc' : 13
	};
	
	/**
		@enum
		@description Represents a week day name used in a Booki.Calendar object.
	*/
	Booki.DayName = {
		'monday': 0
		, 'tuesday': 1
		, 'wednesday': 2
		, 'thursday': 3
		, 'friday': 4
		, 'saturday': 5
		, 'sunday': 6
	};
	
	Booki.Sidenav = {
		'detail': 0
		, 'edit': 1
	};
	
	Booki.CalendarMode = {
		'popup': 0
		, 'inline': 1
		, 'range': 2
		, 'nextDayCheckout': 3
	};
	
	Booki.BookingMode = {
		'reservation': 0
		, 'appointment': 1
	};
	
	Booki.Period = {
		'byDay': 0
		, 'byTime': 1
	};
	Booki.ProjectStatus = {
		'stopped': 0
		, 'running': 1
	};
	/**
		@enum
		@description Represents the direction up or down in a Booki.FormElements collection object.
	*/
	Booki.Direction = {
		'up': 0
		, 'down': 1
	};
	
	Booki.HourFormat = {
		'H24': 0
		, 'AMPM': 1
	};
	
	Booki.ProjectStep = {
		'bookingForm': 0
		, 'customFormFields': 1
	};
})();