	$('.load_content-partial').click(function() {
		var $this = $(this),
			url = $this.data('url'),
			active = $this.hasClass('active');
		$('.collapse_tr').find('.collapse-content').slideUp(active?300:0, function() {
			$(this).parents('.collapse_tr').remove();
			$this.removeClass('active');
		});

		if(!active) {
			if(url) {
				$.ajax({
				    url: url,
				    success: function(data){
				    	
				    	template = "<tr class='collapse_tr'><td colspan='8'>"+data+"</td></tr>";
				    	$('.table-collapse_btn span').removeClass('active');
				    	container = $this.addClass('active').parents('tr').after(template).next('.collapse_tr').find('.collapse-content');
				    	container.slideToggle(400, function() {
	    			    	/* Скроллим, если открытый бокс не влазит на экран */

    		    			var boxPos = $(this).offset().top + $(this).height()-$(window).height();
    		    			var bodyPos = document.body.scrollTop;

    		    			if(boxPos > bodyPos) {
    		    				$("html, body").animate({scrollTop:boxPos+"px"},{duration:200});
    		    			}
				    	});

					}
				});
			}
			
		} 
		
	});