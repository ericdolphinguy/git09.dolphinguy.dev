
Your handlebar context information for handlebars goes in D:\wamp\www\ProjectBookingCalendar\wp-content\plugins\booki\src\com\booki/handlebars/hash.hbs as a JSON string.
Your handlebar partials information for handlebars goes in D:\wamp\www\ProjectBookingCalendar\wp-content\plugins\booki\src\com\booki/handlebars/partial.hbs as a JSON string. 
You can also add entire files containing handlebar partials to parse within separate *.hbs files but these need to be included
inside the partials folder. The name of the partial is the filename less extension eg: base.hbs ( the name of this partial is base ).
Your handlebar base template information for handlebars goes in ${src.dir.handlebars.partials}. 
The handbar base template is going to be inherited by all your pages where you would override values in this base template in each of your pages.
You don't have to do anything ofcourse. The build script takes care of everything else.
			