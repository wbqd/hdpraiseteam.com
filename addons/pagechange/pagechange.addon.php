<?php
  /*
    XE여부 판단하여 XE가 아닌경우 종료
    1.4 호환을 위해 __ZBXE__ 추가
  */
  if(!defined("__ZBXE__") && !defined("__XE__")) exit();
  
  /* 관리자 페이지일 경우 종료 */
  if(Context::get('module')=="admin") return;
  /*
    before_display_content가 아니면 종료
    (콘텐츠 보여주기 전이 아니면 종료)
    애드온은 4번 실행되는데 각자 $called_position 이 다르다.
    
    그래서 아래처럼 제어하여 애드온이 원하는 부분에서 실행되도록 한다.
  */
  if($called_position != "before_display_content") return;
  
  /* 모바일 향상기능 작동 + 모바일에서 PC버전을 볼 경우 종료 */
  if($addon_info->mobilefast == 'Y' && $_COOKIE['mobile'] != "true" && Mobile::isMobileCheckByAgent()) return;
  
  /* fadeIn 기능, fadeOut 기능을 모두 사용안함 처리 했을때 종료 */
  if($addon_info->fadein == "N" && $addon_info->fadeout == "N") return;
  
  /* fadeIn 시간, fadeOut 시간을 설정하지 않은경우 기본값 설정 */
  if(!$addon_info->in_time) $addon_info->in_time = 700;
  if(!$addon_info->out_time) $addon_info->out_time = 700;
    
  /* 사이트 주소 구하기 */
  $url = getFullUrl();
  
  /* 사이트 주소 끝에 / 가 붙어있는경우 / 제거 */
  $len = strlen($url);
  if(substr($url,$len-1,1) == '/') $url = substr($url,0,$len-1);
  
  /* 페이지체인징 핵심코드 시작 */
  $headerPutting = '';
  if($addon_info->fadein != "N")
    /*
      body 페이드 형식에서 레이어 페이드 형식으로 전환.
      (
        닉네임 클릭시 나타나는 레이어가 나타나지 않는 점 해결
        IE7, IE8 브라우저에서 작동 가능해짐
      )
    */
    $headerPutting .= '
      <style type="text/css">
        #pageFade {
          top:0;
          left:0;
          position:fixed;
          width:100%;
          height:100%;
          background:#fff;
          z-index:99999;
        }
      </style>
      <noscript>
        <style type="text/css">
          #pageFade {
            display:none;
          }
        </style>
      </noscript>
    ';
    
    /*
      로딩 표현부분
      로딩 이미지는 xe기본 이미지를 사용
    */
  if($addon_info->loading != "N")
    $headerPutting .= '
      <style type="text/css">
        .pagechange {
          position:absolute;
          top:50%;
          left:50%;
          z-index:99999;
          height:32px;
          overflow:hidden;
          line-height:32px;
          margin:-41px 0 0 -101px;
          padding:24px;
          font-size:20pt;
          border:1px solid #ccc;
          display:block;
          border-radius:20px;
          background:#fff;
          whitespace:nowrap;
        }
        .pagechange img {
          vertical-align:middle;
          margin:0 24px 0 0;
        }
      </style>
    ';
  $headerPutting .= '
    <script type="text/javascript">
      jQuery(function($){
        p=$("#pageFade");
  ';
  if($addon_info->fadein != "N")
    $headerPutting .= '
        $(window).load(function(){
          p.fadeOut('.$addon_info->in_time.');
        });
    ';
  if($addon_info->fadeout != "N")
    $headerPutting .= '
        /*
          키보드가 눌려 있을 경우에는 예외처리
          ctrl+클릭, shift+클릭 등이 작동 안하던 점 해결.
        */
        keyup = true;
        $(window).keydown(function(){
          keyup = false;
        });
        $(window).keyup(function(){
          keyup = true;
        });
        
        /* 주소 처리 함수 */
        function chPage(a){
          if((a.indexOf("'.$url.'") == -1 && a.indexOf("http://") != -1) || a.indexOf("#") == 0)
            return false;
          else return true;
        }
        
        /*
          nopc라는 클래스를 가진경우 예외 처리
          (nopc = no page change)로서 페이지 체인지를 하지 않는 링크는 class에 nopc를 추가하면 된다.
          onclick이벤트 있을 경우 예외처리
          href에 javascript를 사용한 경우 예외처리
          prettyPhoto 사용시 예외 처리
        */
        $("a:not(.nopc, [onclick*=return], [href^=javascript], [rel*=prettyPhoto])").click(function(){
          o = $(this);
          
          /* 스케치북 게시판 뷰어 기능 예외처리 */
          r = $(this).attr("rel");
          if(r) if($.cookie("with_viewer") == "Y" && r.indexOf("listStyle=viewer") != -1) return true;
          
          h = o.attr("href");
          t = o.attr("target");
          if(h){
            /* target이 없으면 _self로 인식 */
            if(!t) t = "_self";
            
            /*
              페이지 바뀜으로 인식하는경우
              (
               주소처리 return값이 true
               target이 _self
               키보드가 눌려 있지 않은 경우
              )
              페이지 체인지 이벤트 실행.
            */
            if(chPage(h) && t == "_self" && keyup){
              p.fadeIn('.$addon_info->out_time.', function(){
                location.href = h;
              });
              return false;
            }
          }
        });
        
        /*
          nopc라는 클래스를 가진경우 예외처리
          (nopc = no page change)로서 페이지 체인지를 하지 않는 폼에는 class에 nopc를 추가하면 된다.
          onsumbit이벤트 있을경우 예외처리
        */
        $("form:not(.nopc, [onsubmit*=return])").submit(function(){
          o = $(this);
          a = o.attr("action");
          /*
            페이지 체인지 이벤트가 실행 된 뒤 submit 된 경우 예외처리하여, submit 작동
          */
          if(chPage(a) && !o.hasClass("isSubmit")){
            /*
              페이지 체인지 이벤트 실행 뒤 submit();
            */
            p.fadeIn('.$addon_info->out_time.',function(){
              o.addClass("isSubmit").submit();
            });
            return false;
          }
        });
    ';
  if($addon_info->fadein != "N" || $addon_info->fadeout != "N")
    $headerPutting .= '
      });
    </script>
    ';
  /* 페이지 상단(빠른 로딩을 위함)에 pagechange와 pageFade 추가 */
  Context::addBodyHeader('<div id="pageFade"><div class="pagechange"><img src="./common/img/msg.loading.gif" alt="Loading" />Loading</div></div>');
  
  /* 모든 브라우저에서 작동 */
  Context::addHtmlHeader($headerPutting);
?>