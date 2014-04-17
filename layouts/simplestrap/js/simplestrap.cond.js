 /* <block cond=""></block> */(function(e,t,n){e('<span class="visible-desktop" style="font-size:1px !important;position:absolute;bottom:0" id="cwspear-is-awesome">.</span>').appendTo("body");var r=function(){return e("#cwspear-is-awesome").is(":visible")},i=e();e.fn.dropdownHover=function(n){i=i.add(this.parent());return this.each(function(){var s=e(this).parent(),o={delay:100,instantlyCloseOthers:!0},u={delay:e(this).data("delay"),instantlyCloseOthers:e(this).data("close-others")},a=e.extend(!0,{},o,n,u),f;s.hover(function(){if(r()){a.instantlyCloseOthers===!0&&i.removeClass("open");t.clearTimeout(f);e(this).addClass("open")}},function(){r()&&(f=t.setTimeout(function(){s.removeClass("open")},a.delay))})})};e(document).ready(function(){e('[data-hover="dropdown"]').dropdownHover()})})(jQuery,this);
<block cond="!$li->xe_css_remove || $li->xe_css_remove=='script'">jQuery("link[rel=stylesheet][href*='common/css/xe.<block cond="!__DEBUG__">min.</block>css']").remove();</block>
jQuery(function($){
$("[data-toggle='tooltip']").tooltip();
$('.tooltip-show-auto').tooltip('show');
<block cond="$li->code_prettify=='Y'">window.prettyPrint && prettyPrint();</block>
<block cond="$li->layout_offcanvas!='N'">
$('#content.panel>.panel-heading [data-toggle=offcanvas]').click(function() {
	$('#main-row').toggleClass('active');
});
</block>
<block cond="!$is_logged && !$is_mobile">
/* $("#dropdown-toggle-login>a").tooltip({title:"{$lang->cmd_login}",placement:"bottom",trigger:"manual"}).tooltip("show").hover(function(){$(this).tooltip("hide");}); */
</block>
<block cond="$li->navbar_fixed!='N' && preg_match('/iPod|iPhone|iPad|Android|BlackBerry|SymbianOS|Bada|Kindle|Wii|SCH-|SPH-|CANU-|Windows Phone|Windows CE|POLARIS|Palm|Dorothy Browser|Mobile|Opera Mobi|Opera Mini|Minimo|AvantGo|NetFront|Nokia|LGPlayer|SonyEricsson|HTC/',getenv('HTTP_USER_AGENT'))">
$(".content textarea,.content input").focus(function(){
	$("#navbar.navbar-fixed-top").css('position','absolute');
}).focusout(function(){
	$("#navbar.navbar-fixed-top").css('position','fixed');
});
</block>
<block cond="$li->site_frame!='content'">
$("#content>.panel-heading>button.close").click(function(){
	$(this).find(".icon").toggleClass("fa-chevron-left fa-chevron-right icon-chevron-left icon-chevron-right");
	if($(this).attr("id") == "sidebar_toggle") {
		if($.cookie("simplestrap_full_frame") == 'true'){
			$("#content").addClass("col-lg-{12-$li->sb_col} col-sm-{12-$li->sb_col}<!--@if($li->site_frame=='sidebar_content')--> col-lg-push-{$li->sb_col} col-sm-push-{$li->sb_col}<!--@end-->").removeClass("col-lg-12 col-sm-12");
			$("#sidebar").removeClass("row").addClass("col col-lg-{$li->sb_col} col-sm-{$li->sb_col}<!--@if($li->site_frame=='sidebar_content')--> col-lg-pull-{12-$li->sb_col} col-sm-pull-{12-$li->sb_col}<!--@end-->")
									.find(".panel").unwrap("<div class='panel-wrap col col-lg-3 col-sm-3'></div>");	
			$.cookie("simplestrap_full_frame",null);
		} else {
			$("#content").removeClass("col-lg-{12-$li->sb_col} col-sm-{12-$li->sb_col}<!--@if($li->site_frame=='sidebar_content')--> col-lg-push-{$li->sb_col} col-sm-push-{$li->sb_col}<!--@end-->").addClass("col-lg-12 col-sm-12");
			$("#sidebar").addClass("row").removeClass("col col-lg-{$li->sb_col} col-sm-{$li->sb_col}<!--@if($li->site_frame=='sidebar_content')--> col-lg-pull-{12-$li->sb_col} col-sm-pull-{12-$li->sb_col}<!--@end-->")
									.find(".panel").wrap("<div class='panel-wrap col col-lg-3 col-sm-3'></div>")
			$.cookie("simplestrap_full_frame",'true');
		}
	}
});
if($.cookie("simplestrap_full_frame") == 'true'){
	$.cookie("simplestrap_full_frame",null);
	$("#sidebar_toggle").click();
}
</block>
<block cond="$li->jumbotron!='N'">
function jumbotron_search_popover(){
$('#jumbotron-search-popover').popover({
	html:true,
	placement:'bottom',
	content:function(){
		return jQuery('#jumbotron-search-popover-content').html();
	}
});
}
jumbotron_search_popover();
$("#jumbotron-hide").click(function(){
	if($.cookie("simplestrap_jumbotron") == "hide"){
		$.cookie("simplestrap_jumbotron",null);
	} else {
		$.cookie("simplestrap_jumbotron","hide");
	}
	$("#jumbotron").toggleClass("jumbotron-hide");
});
</block>
});