( function( api ) {

	// Extends our custom "career-development" section.
	api.sectionConstructor['career-development'] = api.Section.extend( {

		// No events for this type of section.
		attachEvents: function () {},

		// Always make the section active.
		isContextuallyActive: function () {
			return true;
		}
	} );

} )( wp.customize );