$(document).ready(function(){
	//always scroll to the top of the page, ignoring last position
	$(document).one('scroll', function(){
		$('body').scrollTop(0);
	});

	//localize dates, using the origional epoch we have in the database
	$('.log_raw_time').each(function(){
		d 		= new Date($(this).data('date') * 1000);
		date	= d.getFullYear() + '-' 
				+ pad(d.getMonth() + 1) + '-' 
				+ pad(d.getDate()) + ' ' 
				+ pad(d.getHours()) + ':' 
				+ pad(d.getMinutes()) + ':' 
				+ pad(d.getSeconds());
			
		$(this)
			.attr('title', $(this).data('date'))//add title for reference
			.text(date);
	}, $('.localtime').text(' (' + $('.localtime').data('done-text') + ')'));//callback to update gui that were done
	
	//show full hashes on hover
	var hashtr_timeout;
	$('.hashtr').hover(
		function(){
			var that = $(this);
			typeof hashtr_timeout == 'undefined' || clearTimeout(hashtr_timeout);
			hashtr_timeout = setTimeout(function(){
					//add some colspans so that we dont resize the entire table, then hide the rest of the td's
					$(that).parents('td')
							.attr('colspan', '5')
							.nextAll('td').hide();
					that.addClass('hashtr_hover');
					that.animate({
					width: '100%'
				})
			}, 750);
			
		}, 
		function(){
			typeof hashtr_timeout == 'undefined' || clearTimeout(hashtr_timeout);
			$(this).animate(
				{
					width: '125px'
				}, {
					duration: 'fast',
					complete: function() {
						$(this).parents('td').nextAll('td').show();
						$(this).parents('td').attr('colspan', '1');
						$(this).removeClass('hashtr_hover');
				}
			});
		}
	);
	
	//show event table on click
	$('#log_table_parent > tbody > tr').click(function(){
		td1 = $('td:first', $(this)).find('span.show_event');
		switch (td1.text()) {
			case '+':
				td1.text('-');
				$('.event' + td1.data('id')).show();
				pos($(this).offset().top - 30);
				break;
			case '-':
				td1.text('+');
				$('.event' + td1.data('id')).hide();
				break;
		}
	});
	
	//show full event data on click
	var last_ed = new Date().getTime();

	$('.event_data').click(function(e) {
		//if state is closed, fire imediatly
		if ($(this).data('state') == 'closed') {
			event_data($(this));
			return true;
		}
		
		//otherwise, if the last click was less than 500ms ago
		//clear the timeout
		if (new Date().getTime() - last_ed < 500) {
			ed_t = clearTimeout(ed_t);
			return false;
		} 

		//still here? set a timeout and wait to see if the
		//user will click again in the next 500ms
		last_ed = new Date().getTime();
		that = $(this);
		ed_t = setTimeout(function () {
			event_data(that);
		}, 500);
		
		
	});
	
});
//scroll to a position on the page
function pos(pos) {
	$('html, body')
		.scrollTop(pos - 150)
		.animate({
		scrollTop: pos
	}, 'fast');
}
//pad a numeric string, used when localizing dates
function pad(n){
	return n < 10 ? '0' + n : n
} 

//toggle event data
var ed_t;
function event_data(el) {
	//set current time
	switch (el.data('state')) {
		case 'open':
			el.css('height', '50px')
				.data('state', 'closed');
			//scroll to the top of the "parent" tr, adding 30 for our menu bar
			pos(el.closest('table').closest('tr').prev('tr').offset().top - 30);
			break;
		case 'closed':
		default:
			el.css('height', '100%')
				.data('state', 'open');
			//scroll to the top of this el, adding 30 for our menu bar
			pos(el.offset().top - 35);
			break;
	}
}
