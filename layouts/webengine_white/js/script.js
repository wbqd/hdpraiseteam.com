jQuery(function($){
	/************************************
	*                                   *
	*         V A R I A B L E S         *
	*                                   *
	************************************/
	var g = $("#gnb");
	var s = g.find(">.selected");
	var b = $("#layout_banner>#banner_frame>#banner_imgs");
	var l = $("#layout_banner>#banner_frame>#banner_loading");

	/************************************
	*                                   *
	*         F U N C T I O N S         *
	*                                   *
	************************************/
	function gL(o){
		return o.position().left;
	}

	function hid(o){
		o.css("display","none");
		return o;
	}

	function shw(o){
		o.css("display","block");
		return o;
	}

	function zidx(o,a){
		o.css("z-index",a);
		return o;
	}

	function opcy(o,a){
		o.css("opacity",a);
		return o;
	}

	/************************************
	*                                   *
	*         S U B   R E S E T         *
	*                                   *
	************************************/
	function pR(o){
		o.find(">ul").css({
			"left":gL(o)+o.width()/2,
			"marginLeft":-(o.find(">ul").width())/2
		});
	}

	/************************************
	*                                   *
	*       G N B   S E T T I N G       *
	*                                   *
	************************************/
	opcy(g.find("ul"),0);


	/************************************
	*                                   *
	*          S U B   L O A D          *
	*                                   *
	************************************/
	$(window).resize(function(){
		if(g.find(">.hover").length > 0){
			pR(g.find(">.hover"));
		}
	});

	/************************************
	*                                   *
	*  G N B   H O V E R   E F F E C T  *
	*                                   *
	************************************/
	g.find("li").hover(function(){
		o = $(this);
		shw(zidx(o.find(">ul").stop().css({
			"left":gL(o)+o.width()/2,
			"marginLeft":-(o.find(">ul").width())/2
		}), 6)).animate({"opacity":1}, 300);
	}, function(){
		zidx($(this).find(">ul").stop(),5).animate({"opacity":0}, 300, function(){
			hid($(this));
		});
	});


	/************************************
	*                                   *
	*            B A N N E R            *
	*                                   *
	************************************/
	function bR(n, d){
		if(d === null) d = 1;
		if(n>=b.children().length && d > 0) n=0;
		else if(n<0 && d < 0) n=b.children().length-1;
		
		$.cookie("ww_banner_img", n);
		b.find(">.selected").stop().animate({"opacity":0}, 600, function(){
			hid($(this));
		}).removeClass("selected");
		opcy(shw(b.find(">.banner_obj_"+n).stop()), 0).animate({"opacity":1}, 600).addClass("selected");
		
		l.stop().css("width", 0).animate({"width":"100%"}, 5000, "linear", function(){
			bR(n+1, 1);
		});
	}
	
	$("#banner_frame").hover(function(){
		$(this).find(">#banner_control:not(.hidden)>.control").stop().animate({"opacity":0.7}, 500);
	}, function(){
		$(this).find(">#banner_control:not(.hidden)>.control").stop().animate({"opacity":0.2}, 500);
	});
	
	$("#banner_left_control").click(function(){
		bR(b.find(">.selected").index()-1, -1);
	});
	
	$("#banner_right_control").click(function(){
		bR(b.find(">.selected").index()+1, 1);
	});
	
	$(window).unload(function(){
		$.cookie("ww_banner_rest", l.width());
	});
	
	if(b.find("banner_obj").length > 1){
		if($.cookie("ww_banner_rest")){
		    var r = $.cookie("ww_banner_rest");
		    var t = 5000-(Number(r)/787)*5000;
		    
		    l.stop().width(r).animate({"width":"100%"}, t, "linear", function(){
		    	bR(Number($.cookie("ww_banner_img"))+1, 1);
		    });
		}
		else {
		    l.stop().css("width", 0).animate({"width":"100%"}, 5000, "linear", function(){
		    	bR(0);
		    });
		    $.cookie("ww_banner_img", 0);
		}
	}
	
	/************************************
	*                                   *
	*             L O G I N             *
	*                                   *
	************************************/
	function lc(){
		if($.trim($("#layout_login #user_id").val()) == "") alert("아이디를 입력해주세요.");
		else if($.trim($("#layout_login #user_pw").val()) == "") alert("비밀번호를 입력해주세요.");
		else return true;
		return false;
	}
	
	$("#layout_login #layout_login_submit").click(function(){
		if(lc()) $("#layout_login").submit();
		return false;
	});
	
	$("#layout_login #user_id, #layout_login #user_pw").keydown(function(e){
		if(e.keyCode == 13){
			lc() && $("#layout_login").submit();
    		return false;
		}
	});
	
	$("#layout_keep_label").click(function(){
		var a = $("#layout_keep").is(":checked");
		$(this).find("span").text(a?"Off":"On").addClass(a?"off":"on").removeClass(a?"on":"off");
		if(!a) $.layout_alert($("#layout_keep").attr("data-msg").replace(/(\\n)/gi, "<br />"));
	});
	
	$.layout_alert = function(msg, type, delay, func){
		var type;
		if(delay === undefined) delay = 3000;
		if(typeof delay === "function") func = delay;
		if(type === undefined) type = "alert";
		else type +=  " alert";
		
		var o = $("<div class=\""+type+"\">"+msg+"</div>");
		if($("#layout_alert .alert:first").length) o.insertBefore($("#layout_alert .alert:first"));
		else o.appendTo($("#layout_alert"));
		o.animate({paddingTop:"show", paddingBottom:"show", marginTop:"show", marginBottom:"show",height:"show"}, 300, function(){
			if(typeof func === "function") func($(this));
		});
		if(typeof func !== "function")
			setTimeout(function(){
				o.animate({paddingTop:"hide", paddingBottom:"hide", marginTop:"hide", marginBottom:"hide",height:"hide"}, 300, function(){
					$(this).remove();
				});
			}, delay);
	}
});
