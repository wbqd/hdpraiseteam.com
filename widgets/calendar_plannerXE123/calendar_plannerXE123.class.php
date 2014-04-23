<?php
/**
##
## @Package:    calendar_plannerXE123 (widget)
## @File name:	calendar_plannerXE123.class.php
## @Author:     Keysung Chung (keysung2004@gmail.com)
## @Copyright:  © 2009 Keysung Chung(keysung2004@gmail.com)
## @Contributors: Clements J. SONG (http://clements.kyunggi.ca/ , clements_song@hotmail.com)
## @Release:	under GPL-v2 License.
## @License:	http://www.opensource.org/licenses/gpl-2.0.php
##
## Redistribution and use in source and binary forms, with or without modification,
## are permitted provided that the following conditions are met:
##
## Redistributions of source code must retain the above copyright notice, this list of
## conditions and the following disclaimer.
## Redistributions in binary form must reproduce the above copyright notice, this list
## of conditions and the following disclaimer in the documentation and/or other materials
## provided with the distribution.
##
## Neither the name of the author nor the names of contributors may be used to endorse
## or promote products derived from this software without specific prior written permission.
##
## THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY
## EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
## MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
## COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
## EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE
## GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED
## AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
## NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED
## OF THE POSSIBILITY OF SUCH DAMAGE.
##
## [changes]
##  - 위젯 PlannerXE123 ver. 4.3.0 : Keysung Chung, 2014, 01, 01
##
**/
	class calendar_plannerXE123 extends WidgetHandler {
        /**
         * @brief 위젯의 실행 부분
         *
         * ./widgets/위젯/conf/info.xml 에 선언한 extra_vars를 args로 받는다
         * 결과를 만든후 print가 아니라 return 해주어야 한다
         **/
        function proc($args) {

			$XE_version = __ZBXE_VERSION__;
			if (!$XE_version) { $XE_version = __XE_VERSION__; }
			$var_version = "PlannerXE123 Widget V430(" .$XE_version. "+".phpversion() . ")";
			Context::loadLang($this->widget_path."lang/"); // loadding language pack.(V430)
			$obj = new stdClass;

            // loged member --- -->
            // 로그인 정보를 구함
            $logged_info = Context::get('logged_info');
            $member_temp_name = $logged_info->nick_name; // 공개그룹 user에 nick_name 이용
            // $member_temp_name = $logged_info->user_name; //  공개그룹 user에 user_name 이용
            $member_srl = $logged_info->member_srl;
			$group_list = $logged_info->group_list;
            $usergroup_arr = array();

			if ($member_srl != null) {
				$obj->var_member_srl = $member_srl;	 // user srl
				foreach($group_list as $key => $val) {
					$group_titles .= ",".$val;
				}
				$group_titles=substr($group_titles,1);    // 사용자가 소속된 그룹명칭
				$usergroup_arr = explode(",",$group_titles);
			}

			// Grant 정보를 구함
            $grant = Context::get('grant');
            $login_site_admin = $grant->is_site_admin; //로그인 당시의 관리자 권한(어드민?)

            // 대상 모듈 (mid_list는 기존 위젯의 호환을 위해서 처리하는 루틴을 유지. module_srls로 위젯에서 변경)
            $oModuleModel = &getModel('module');
            if($args->mid_list) {
                $mid_list = explode(",",$args->mid_list);
                if(count($mid_list)) {
                    $module_srls = $oModuleModel->getModuleSrlByMid($mid_list);
                    if(count($module_srls)) $args->module_srls = implode(',',$module_srls);
                    else $args->module_srls = null;
                }
            }
            // 대상 모듈중 상담기능설정, 문서보기종류, 스킨폴더명, 카테고리색상사용 정보 추출
            if($args->module_srls) {
				$oModuleModel_temp = &getModel('module');
				$module_srls_arr = explode(",",$args->module_srls);

				foreach($module_srls_arr as $key => $val_temp) {
					$module_info_temp = $oModuleModel_temp->getModuleInfoByModuleSrl($val_temp);
					$board_skin_arr[$val_temp] = "modules/".$module_info_temp->module."/skins/".$module_info_temp->skin; //스킨 폴더명
	                $grant_temp = $oModuleModel_temp->getGrant($module_info_temp, $logged_info);
					if($grant_temp->manager){
						$board_manager_srls .= ",".trim($val_temp);  // 관리권한있는 게시판 srl 리스트
					}
					if($module_info_temp->consultation == 'Y') {
						$board_consul_srls .= ",".trim($val_temp);  // 상담기능 게시판 srl 리스트
					}

					$oModuleModel_temp->syncSkinInfoToModuleInfo($module_info_temp);	//모듈 확장변수(skin)연계
					$document_group_arr[trim($val_temp)] = $module_info_temp->default_document_group; //기본보기 문서종류

					// 분류(카테고리) 사용 : $module_info_temp->hide_category 조건 제거(호환성을 위해)
					if($module_info_temp->use_category_bgcolor == 'category_bg' || $module_info_temp->use_category_bgcolor == 'category_text') {
							$ind_use_category = 'Y';
					} else {
							$ind_use_category = 'N';
					}
					$board_use_category_color_arr[trim($val_temp)] = $ind_use_category.",".$module_info_temp->use_category_bgcolor; //카테고리사용및 배경색 옵션

					if($module_info_temp->image_diary == 'Y' || $module_info_temp->image_diary == 'F') { // 그림일기 게시판 섬네일 옵션
						$board_imagediary_arr[trim($val_temp)] = $module_info_temp->image_diary.",".$module_info_temp->thumbnail_width.",".$module_info_temp->thumbnail_height.",".$module_info_temp->thumbnail_type;
					}
					$display_complete_arr[trim($val_temp)] = $module_info_temp->display_complete_doc; //완료일정 표시여부

					// get country & offday information by board
					$board_country_arr[trim($val_temp)] = $module_info_temp->holiday_country;// 휴일국가
					if($module_info_temp->off_day ==""){
						$board_offday_arr[trim($val_temp)] = array();// 휴무일
					} else {
						$board_offday_arr[trim($val_temp)] = unserialize($module_info_temp->off_day);// 휴무일
					}
					if($module_info_temp->off_day_option ==""){
						$board_offday_option_arr[trim($val_temp)] = array();// 휴무일 처리옵션
					} else {
						$board_offday_option_arr[trim($val_temp)] = unserialize($module_info_temp->off_day_option);// 휴무일 처리옵션
					}
				}

				$board_consul_srls = substr($board_consul_srls,1);
				$board_consul_arr = explode(",", $board_consul_srls);	// 상담기능 게시판 srl 어레이
				$board_manager_srls = substr($board_manager_srls,1);
				$board_manager_arr = explode(",", $board_manager_srls);	// 관리권한있는 상담기능 게시판 srl 어레이
			}
			//$widget_info->testfld = $board_use_category_color_arr;  //디버깅을 위해
			//$widget_info->testfld = $document_group_arr;  //디버깅을 위해
			//$widget_info->testfld .= $args->module_srls."<br/>상담기능: (".$board_consul_srls.")"."<br/>관리기능: (".$board_manager_srls.")";  //디버깅을 위해

            // 위젯 자체적으로 설정한 변수들을 체크
            // 보기 옵션
            $option_view_arr = explode(',',$args->option_view);

            // 기본 형태
            $default_style = $args->default_style;

            // 플래너 기본 출력 단위
			if ($args->default_view == null) {
				$default_view = "M";
			} else {
				$default_view = $args->default_view;
			}
            // 타임 스케줄 출력여부
			if ($args->display_timeschedule != "N") {
				$dispTimeSchedule = "Y";
			}

            // 당일부터출력(리스트형)
            $ind_weekly_base = $args->weekly_base;

            // 내용 일부출력(리스트형)
            $ind_display_detail = $args->display_detail;

            // 기본 휴일적용 국가
            $dft_country = $args->holiday_country;

            // 휴일적용 국가 선택박스 출력여부
            $display_country_select = $args->display_country_select;

            // 배경 색상
            $bg_color = $args->bg_color;

            // 제목
            $title = $args->title;

            // 정렬 대상
            $order_target = $args->order_target;
            if(!in_array($order_target, array('list_order','update_order','extra_value_end'))) $order_target = 'extra_value_end';

            // 정렬 순서
            $order_type = $args->order_type;
            if(!in_array($order_type, array('desc','asc'))) $order_type = 'asc';

            // 출력된 목록 수
            $list_count = (int)$args->list_count;
            if(!$list_count) $list_count = 400;

            // 제목 길이 자르기
            $subject_cut_size = $args->subject_cut_size;
            if(!$subject_cut_size) $subject_cut_size = 0;

            // 내용 길이 자르기
            $content_cut_size = $args->content_cut_size;
            if($content_cut_size == null) $content_cut_size = 240;

            // 최근 글 표시 시간
            $duration_new = $args->duration_new;
            if(!$duration_new) $duration_new = 24;

            // 툴팁 출력 유저
            $display_tooltip = $args->display_tooltip;
            if(!$display_tooltip) $display_tooltip = 'all';

			// 대상 모듈이 선택되어 있지 않으면 해당 사이트의 전체 모듈을 대상으로 함
            $site_module_info = Context::get('site_module_info');
            if($args->module_srls) $obj->module_srl = $args->module_srls;
            else if($site_module_info) $obj->site_srl = (int)$site_module_info->site_srl;

            // 모듈의 정보를 구함
            $module_info = $oModuleModel->getModuleInfoByModuleSrl($obj->module_srl);

			// 플래너에 필요한 함수 include
            // 플래너 함수
			if (!class_exists('planner123_widget_main')) {
				require_once ('function/class.planner123_widget_main.php');
			}

			//get parameter
			parse_str($_SERVER['QUERY_STRING'], $query_srt);


// planner123 문서목록 얻기 ---------------------------------- -->
			// Daylight saving time을 위해 Time zone을 지정할 수 있음 ( php4.대에서는 에러 발생, php5.1 이상에서 정상작동)
			// 타임존목록 http://kr.php.net/manual/kr/timezones.php )예: America/Toronto
			// date_default_timezone_set('America/Toronto');
            // 당일 타임 스탬프 (타임존 고려)
			$server_timestamp = mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y"));
			$server_offset = date("Z");
			$xe_timestamp = planner123_widget_main::fn_xetimestamp();
			$xe_offset = date("Z")+zgap();
			$client_offset = $query_srt[offset];  // url 변수로 받은 client offset
			$client_timestamp = mktime(date("H"), date("i"), date("s")-date("Z")+$client_offset, date("m"), date("d"), date("Y"));

			if ($client_offset == null || $client_offset == 0) {
				$pTodaystamp = $xe_timestamp;	// XE time 오늘을 처리 기준일로..
			} else {
				$pTodaystamp = $client_timestamp;// Client Time 오늘을 처리 기준일로..
			}
			$TodayYear = date("Y", $pTodaystamp);	// 오늘의 년도
			$TodayMonth = date("n", $pTodaystamp);	// 오늘의 월
			$TodayDay = date("j", $pTodaystamp);	// 오늘의 일

			// 시작할 날자 재계산 (pYear, pMonth에 따른..)
			$ind_fill ='';
			if ($query_srt[pYear] == null) {	// 변수 pYear에 값이 없으면..
				$pYear = $TodayYear;	// 당년
			} else {
				$pYear = $query_srt[pYear];	// 선택한 년도
			}
			if ($query_srt[pMonth] == null) {	// 변수 pMonth 에 값이 없으면..
				$pMonth = $TodayMonth;	// 당월
			} else {
				$pMonth = $query_srt[pMonth];	//선택한 월
			}
			if ($query_srt[pDay] == null) {	// 변수 pDay 에 값이 없으면..
				if ($query_srt[pMonth] != null) {	// 월이동단추 클릭이면 1일부터..
					$pDay = 1;			// 1일
					$ind_fill ='Y';
				} else {
					$pDay = $TodayDay;	// 오늘
					$tmp_feb_lastday = date('t', mktime(0,0,0,$pMonth,1,$pYear));
					if($pDay > $tmp_feb_lastday) {
						$pDay = $tmp_feb_lastday;
					}
				}
			} else {
				$pDay = $query_srt[pDay];	//선택한 일
			}

            if ($default_style == 'mini') { // 미니달력 당월 익월
				$pMonth += $args->mini_month_option;
			}

			$pTimestamp = mktime(0, 0, 0, $pMonth, $pDay, $pYear); // 시작일자 타임스탬프
			$pYear = date("Y", $pTimestamp);	// 년
			$pMonth = date("n", $pTimestamp);	// 월
			$pDay = date("j", $pTimestamp);		// 일
			$tmp_weekday = date("w", $pTimestamp);	// 처리일 요일 (일=0..토=6)

			// 주단위, 월단위에따른 시작, 종료일 계산
			$tmp_lastday = date("t", $pTimestamp);//말일
			if(function_exists('gregoriantojd')) {  // PHP 컴파일시 calendar library 누락 감안
				$jd_today = gregoriantojd($TodayMonth, $TodayDay, $TodayYear); // today jd
				$jd_dspStart = gregoriantojd($pMonth, $pDay, $pYear); // 출력시작 일자 jd
				$jd_dspEnd = gregoriantojd($pMonth, $tmp_lastday, $pYear); // 출력종료 일자 jd
			} else {
				$jd_today = planner123_widget_main::fn_calcDateToJD($TodayYear, $TodayMonth, $TodayDay); // today jd
				$jd_dspStart = planner123_widget_main::fn_calcDateToJD($pYear, $pMonth, $pDay); // 출력시작 일자 jd
				$jd_dspEnd = planner123_widget_main::fn_calcDateToJD($pYear, $pMonth, $tmp_lastday); // 출력종료 일자 jd
			}

			if ($query_srt[pOption_2] != 'M' && $query_srt[pOption_2] != 'W2' && $query_srt[pOption_2] != 'W1') {
				$ind_pOption_2 = $default_view; // widget default value
			} else {
				$ind_pOption_2 = $query_srt[pOption_2];	// url value
			}

			// $ind_pOption_2 값은 항상 M, W1, W2중 하나임
			if ($default_style == 'mini') { // M: 월 단위 출력  (미니달력 일때는 항상 월단위로...)
				$ind_pOption_2 = 'M';	// monthly
				$jd_dspStart = $jd_dspStart - $pDay +1; // 출력시작 일자 jd (1일)
				$jd_dspEnd += 2; //말일 2일 후
			} else if ($ind_pOption_2 == 'W1') { // W1: 1주 단위 출력
				$ind_pOption_2 = 'W1';	// 1week
				if($default_style == 'list' && $ind_weekly_base > 0){
					 $jd_dspEnd = $jd_dspStart + 7-1; //오늘부터 7일
				} else {
					$jd_dspStart = $jd_dspStart - date("w", $pTimestamp); // 출력 시작일 해당주 일요일
					$jd_dspEnd = $jd_dspStart + 7-1; // 출력 종료일 해당주 토요일
				}
			} else if ($ind_pOption_2 == 'W2') { // W2: 2주 단위 출력
				$ind_pOption_2 = 'W2';	// 2week
				if($default_style == 'list' && $ind_weekly_base > 0) {
					$jd_dspEnd = $jd_dspStart + 14-1;
				} else {
					$jd_dspStart = $jd_dspStart -  date("w", $pTimestamp); // 출력 시작일 해당주 일요일
					$jd_dspEnd = $jd_dspStart + 14-1; // 출력 종료일 다음주 토요일
				}
			} else { // M: 월 단위 출력
				$ind_pOption_2 = 'M';	// monthly
				if($default_style == 'list') {
					if($ind_weekly_base > 0) {
						$jd_dspEnd = $jd_dspStart + 7 * $ind_weekly_base -1;
					} else {
						$jd_dspStart = $jd_dspStart - $pDay +1; // 출력시작 일자 jd (1일)
					}
				} else {
					if($ind_weekly_base > 0) {
						$jd_dspStart = $jd_dspStart - date("w", $pTimestamp); // 출력 시작일 해당주 일요일
						$jd_dspEnd = $jd_dspStart + 7 * $ind_weekly_base - 1; // 출력 종료일 n주 토요일
					} elseif($args->firstlast_week !="alldate") {     // 첫주및 마지막주: "empty":당월만 표시, "alldate":전부 표시
						$jd_dspStart = $jd_dspStart - $pDay + 1; // 출력시작 일자 jd (1일)
					} else {
						$jd_dspStart = $jd_dspStart - $pDay + 1 - date("w", mktime(0,0,0,$pMonth,1,$pYear));
						$jd_dspEnd = $jd_dspEnd + (6 - date('w', mktime(0,0,0,$pMonth,$tmp_lastday,$pYear)));
						$ind_fill = 'Y'; // fill calender
					}
				}
			}

			if(function_exists('jdtogregorian')) { // PHP 컴파일시 calendar library 누락 감안-->
				$wrk_dsp_str = jdtogregorian($jd_dspStart);
				$wrk_dsp_end = jdtogregorian($jd_dspEnd);
			} else {
				$wrk_dsp_str = planner123_widget_main::fn_calcJDToGregorian($jd_dspStart);
				$wrk_dsp_end = planner123_widget_main::fn_calcJDToGregorian($jd_dspEnd);
			}

			$wrk_arr_dt = explode('/', $wrk_dsp_str);
			$dispStart_stamp =  mktime(0, 1, 0, $wrk_arr_dt[0], $wrk_arr_dt[1], $wrk_arr_dt[2]);// 출력 시작일
			$wrk_arr_dt = explode('/', $wrk_dsp_end);
			$dispEnd_stamp =  mktime(23, 59, 0, $wrk_arr_dt[0], $wrk_arr_dt[1], $wrk_arr_dt[2]);// 출력 종료일

			// 시작, 종료일자를 구하고...
			$dispStartYY = date("Y", $dispStart_stamp);	// 출력 시작일 년도
			$dispStartMM = date("m", $dispStart_stamp);
			$dispStartDD = date("d", $dispStart_stamp);
			$dispEndYY = date("Y", $dispEnd_stamp);	// 출력 종료일 년도
			$dispEndMM = date("m", $dispEnd_stamp);
			$dispEndDD = date("d", $dispEnd_stamp);
			//디버깅을 위해
			//$widget_info->testfld = date('r',$pTimestamp)."/".date('r',$dispStart_stamp)."/".date('r',$dispEnd_stamp)."/ :".$ind_pOption_2." ".$default_style."<br/>";

			// 년단위 반복일정만 select하기 위해
			if ($dispEndMM >= $dispStartMM) {
				$tmp_slt_s_mmdd = $dispStartMM.$dispStartDD;
				$tmp_slt_e_mmdd = $dispEndMM.$dispEndDD;
				$tmp_slt_s_mmdd2 = '0000';
				$tmp_slt_e_mmdd2 = '0000';
			} else {  // 선택 범위 종료월이 시작월보다 작을때를 고려 예:12월말-->
				$tmp_slt_s_mmdd = $dispStartMM.$dispStartDD;
				$tmp_slt_e_mmdd = '1231';
				$tmp_slt_s_mmdd2 = '0101';
				$tmp_slt_e_mmdd2 = $dispEndMM.$dispEndDD;
			}

			// 쿼리 설정/실행/list만들기--- -->
			$obj->var_0 = '0';
			$obj->sort_index_1 = 'extra_value_end';	// (일정종료 일)
			$obj->order_type_1 = 'desc';
			$obj->sort_index_default_1 = 'extra_value_end';
			$obj->sort_index_1_1 = 'extra_value_start';	// (일정종료 일)
			$obj->order_type_1_1 = 'desc';
			$obj->sort_index_default_1_1 = 'extra_value_start';
			$obj->sort_index_2 = 'extra_value_time';	// (시작종료 시간)
			$obj->order_type_2 = 'asc';
			$obj->sort_index_default_2 = 'extra_value_time';
			$obj->sort_index = $order_target;
			$obj->order_type = $order_type=="desc"?"asc":"desc";
			$obj->sort_index_default = 'extra_value_start';	// 선택값이 null 일때
			$obj->list_count = $list_count;  // 플래너에 표시될 일정수 (예:기본 200개)
			$obj->var_period_start = $dispStartYY.$dispStartMM.$dispStartDD;	 // 선택 범위시작 - 기간 시작일 >=
			$obj->var_period_end = $dispEndYY.$dispEndMM.$dispEndDD;	 // 선택 범위  끝 - 기간 마지막날 <=
			$obj->var_start_mmdd = $tmp_slt_s_mmdd;	    // 년단위 반복일정만 있는경우 선택 범위시작
			$obj->var_end_mmdd = $tmp_slt_e_mmdd;		// 년단위 반복일정만 있는경우 선택 범위끝
			$obj->var_start_mmdd2 = $tmp_slt_s_mmdd2;	// 년단위 반복일정만 있는경우 선택 범위시작 2
			$obj->var_end_mmdd2 = $tmp_slt_e_mmdd2;		// 년단위 반복일정만 있는경우 선택 범위 끝	2
			$obj->var_fld_null = null;	 // 0을 null로 (추후 값이없는 확장변수 레코드 삭제를 고려하여 null로 변경)

			$tmp_01=explode("/",$this->widget_path);
			$query_path = $tmp_01[1].'.'.$tmp_01[2];       // 쿼리경로

			// 각 게시판에 설정된 권한에 따라 문서를 분류 하기위해 당월에 일정이 있는 문서를 전부 불러옴
			// (위젯에서 별도로 권한주는것은 취소)
			$output = executeQueryArray($query_path.'.getDocumentsForPlanner_all', $obj);    // 모든문서

			// document 모듈의 model 객체를 받아서 결과를 객체화 시킴
            $oDocumentModel = &getModel('document');

			// 오류가 생기면 그냥 무시(메시지 넘김으로 수정)
            if(!$output->toBool()) {
				$widget_info->testfld .= "query error: (".$dispStartYY."-".$dispStartMM."-".$dispStartDD.",".$order_target.",".$order_type.",".$query_path.")";  //디버깅을 위해
			//	return;
			}

			// ---권한에 따른 문서 목록 생성 --
            $modules = array();
			if(count($output->data)) {
				foreach($output->data as $key => $attribute) {
                    $modules[$attribute->module_srl]->mid = $attribute->mid;
                    $modules[$attribute->module_srl]->site_srl = $attribute->site_srl;
					$oDocument_srl = $attribute->document_srl;

					$oDocument = new documentItem();
					$oDocument->setAttribute($attribute, false);
					$oDocument->category_srl = $attribute->category_srl;
					$oDocument->module_srl = $attribute->module_srl;
					$oDocument->module_skin = $board_skin_arr[$attribute->module_srl];
					$oDocument->use_category_bgcolor = $board_use_category_color_arr[$attribute->module_srl];
					$oDocument->image_diary = $board_imagediary_arr[$attribute->module_srl];
					$oDocument->display_complete_doc = $display_complete_arr[$attribute->module_srl];

					$tmp_opengroup_arr = explode("|@|",$attribute->extra_value_group);

					// 게시판에 설정된 문서보기 종류에따라 보여줄문서여부 판단
					if (in_array($oDocument->module_srl, $board_consul_arr)) { // 상담기능 게시판 문서일때
						if ($login_site_admin) {					// 로그인시 관리자(어드민?)
						    $document_list[$key] = $oDocument;      // 로그인시 관리자(어드민?)이면 문서를 처리,
						} else if(in_array($oDocument->module_srl, $board_manager_arr)){
						    $document_list[$key] = $oDocument;      // 관리자 권한있는 게시판이면 문서를 처리,
						} else if ($attribute->is_notice == "Y" || $attribute->member_srl == $member_srl) {
							$document_list[$key] = $oDocument;      //공지문서나 자신의 문서이면 처리.
						}
					} else {  // 상담기능 게시판 문서가 아닐때

						if ($login_site_admin) {					// 로그인시 관리자(어드민?)
						    $document_list[$key] = $oDocument;      // 로그인시 관리자(어드민?)이면 문서를 처리,
						} else if(in_array($oDocument->module_srl, $board_manager_arr)){
						    $document_list[$key] = $oDocument;      // 관리 권한있는 게시판의 문서이면 처리,
						} else if ($attribute->is_notice == "Y" || $attribute->member_srl == $member_srl) {
							$document_list[$key] = $oDocument;      //공지문서나 자신의 문서이면 처리.
						} else {									// 관리자도 아니고 권한도 없다면, 기본 보기문서 종류에 따라 처리

							switch ($document_group_arr[$oDocument->module_srl]) {  //각 게시판의 기본보기 문서종류
							case "":								// 문서그룹이 없으면 (Ver 1.0 이전버전 감안),
								$document_list[$key] = $oDocument;  // 문서그룹이 alldocument 이면 문서를 처리,
								break;
							case "alldocument":
								$document_list[$key] = $oDocument;  // 문서그룹이 alldocument 이면 문서를 처리,
								break;
							case "nonsecured":						//문서그룹이 nonsecured 일때
								$tmp_flag = null;
								$tmp_group_01 = $tmp_opengroup_arr[0];
								if ($attribute->is_notice == "Y" || $attribute->member_srl == $member_srl) {
		 							$document_list[$key] = $oDocument;       //공지문서나 자신의 문서이면 처리.(위에서처리됨)
								} else if($attribute->is_secret == "N" || $attribute->status == "PUBLIC"){
									if($tmp_opengroup_arr == null || $tmp_group_01 == null) { // 공개그룹 없는 일반문서
										$tmp_flag = "*";
									} else if(count($tmp_opengroup_arr) == 1 && ($attribute->nick_name == $tmp_group_01 || $attribute->user_name == $tmp_group_01)) {
										$tmp_flag = "*";
									} else {
										foreach($usergroup_arr as $tmp_key => $val_tmp) {
						    				if (in_array($val_tmp, $tmp_opengroup_arr)) {
												$tmp_flag = "*";            //공개그룹 소속.
											}
										}
						    			if ($member_temp_name != null && in_array($member_temp_name, $tmp_opengroup_arr)){
											$tmp_flag = "*";                        // USER 개인공개.
										}
									}
									if ($tmp_flag == "*") {
			 							$document_list[$key] = $oDocument;  //공개그룹 소속이면 처리.
									}
								}
								break;
							case "usergroup":								//문서그룹이 usergroup 일때
								$tmp_flag = null;
								if ($attribute->is_notice == "Y" || $attribute->member_srl == $member_srl) {
		 							$document_list[$key] = $oDocument;       //공지문서나 자신의 문서이면 처리.(위에서처리됨)
								} else if($attribute->is_secret == "N" || $attribute->status == "PUBLIC"){
									$tmp_flag = null;
									foreach($usergroup_arr as $tmp_key => $val_tmp) {
						    		    if (in_array($val_tmp, $tmp_opengroup_arr)) {
											$tmp_flag = "*";                 //공개그룹 소속.
										}
									}
							    	if ($member_temp_name != null && in_array($member_temp_name, $tmp_opengroup_arr)){   //USER 개인공개.
										$tmp_flag = "*";
									}
									if ($tmp_flag == "*") {
			 					        $document_list[$key] = $oDocument;   //공개그룹 소속이면 처리.
									}
								}
								break;
							case "owner":									//문서그룹이 usergroup 일때
								if ($attribute->is_notice == "Y" || $attribute->member_srl == $member_srl) {
		 					        $document_list[$key] = $oDocument;      //공지문서나 자신의 문서이면 처리.(위에서처리됨)
								}
								break;
						  }  // switch 끝
						}	// 관리자도 아니고 권한도 없을때 끝
					} // 상담기능 게시판 문서가 아닐때 끝
				//echo $oDocument_srl." ";  //디버깅용
				}  // foreeach 끝

                $oDocumentModel->setToAllDocumentExtraVars();
			} else {
				$document_list = array();
			}
// planner123 문서목록 얻기 끝---------------------------------- -->

            // 분류 구함
            $output_category = executeQueryArray($query_path.'.getCategories', $obj_2);
			$category_list = array();
            if($output_category->toBool() && $output_category->data) {
                foreach($output_category->data as $key => $val) {
                    $category_list[$val->category_srl] = $val;
                }
            }

			// 입력된 메인일정 게시판 모듈이 있으면 이것을 메인 일정으로(모듈이 여러개 선택 되었을 경우 사용)
			$main_schedule_module = $args->main_schedule_module;
			if($main_schedule_module != null) {
				$widget_info->mid = $main_schedule_module;
            } else {
				$tmp_minfo = $oModuleModel->getModuleInfoByModuleSrl($module_srls_arr[0]);
				$widget_info->mid = $tmp_minfo->mid;
			}

            // 휴일/기념일 함수
			$country_name_arr = array("kor" => "Korea", "usa"=> "U.S.A", "chn"=> "China", "jpn"=> "Japan", "can"=> "Canada", "vnm"=> "Vietnam", "tur"=> "Turkey", "user"=> "User", "default"=> "Default");
			if ($args->holiday_country == "") {
				$args->holiday_country = "default";
			}
			$dft_country_code = $args->holiday_country;

			if ($default_style == 'mini') {
				$holiday_country_code = $dft_country_code;// 디폴트 휴일/기념일 국가
			} else {
				if ($query_srt[pHoliday_cnt] != null) {	// 변수 pHoliday_cnt 에 값이 있으면..
					$holiday_country_code = $query_srt[pHoliday_cnt];
				} else {
					$holiday_country_code = $dft_country_code;// 디폴트 휴일/기념일 국가
				}
			}

			$tmp_path = $this->widget_path;
			$holiday_filename = planner123_widget_main::fn_getHolidayFileName($tmp_path."function/",$holiday_country_code);
			if ($holiday_filename == 'class.planner123_widget_holiday_default.php') {
				$holiday_country_code = 'default';
			}
			$holiday_country_name = $country_name_arr[$holiday_country_code];

            // 휴일 data by country
			$HolidayByCountry[$dft_country_code] = planner123_widget_main::fn_getHolidayByCountry($tmp_path."function/", $dft_country_code, $dispStart_stamp, $dispEnd_stamp);
			if (!array_key_exists($holiday_country_code, $HolidayByCountry)) {
 				$HolidayByCountry[$holiday_country_code] = planner123_widget_main::fn_getHolidayByCountry($tmp_path."function/", $holiday_country_code, $dispStart_stamp, $dispEnd_stamp);
			}
			foreach($board_country_arr as $key => $val) {
				if (!array_key_exists($val, $HolidayByCountry)) {
 					$HolidayByCountry[$val] = planner123_widget_main::fn_getHolidayByCountry($tmp_path."function/", $val, $dispStart_stamp, $dispEnd_stamp);
				}
			}
			// 기념일 data
            if ($default_style != 'mini') {
				$Memday_arr = planner123_widget_main::fn_getMemdayByCountry($tmp_path."function/", $holiday_country_code, $dispStart_stamp, $dispEnd_stamp);
			}
			//$widget_info->testfld = $holiday_country_code;

			// 위젯설정 offday 옵션(V430)
			$dft_offday_option = explode(',', $args->off_day_option);
			$dft_offday = explode(',', $args->off_day);		// 위젯설정 offday 요일
			if(count($dft_offday_option)) {
				if(in_array("label", $dft_offday_option)){	// 휴무일 표시(V430)
					$ind_offday_label = TRUE;
				}
				if(in_array("na_new", $dft_offday_option)){	// 휴무일 신규일정 불허(V430)
					$ind_offday_naNew = TRUE;
				}
				if(in_array("h", $dft_offday)){				// 공휴일 휴무(V430)
					$ind_holidayisOffday = TRUE;
				}
			}

            // 템플릿 파일에서 사용할 변수들을 세팅
			$widget_info->option_view_arr = $option_view_arr;
			$widget_info->bg_color = $bg_color;
            $widget_info->title = $title;
            $widget_info->document_list = $document_list;
            $widget_info->category_list = $category_list;
            $widget_info->subject_cut_size = $subject_cut_size;
            $widget_info->content_cut_size = $content_cut_size;
            $widget_info->duration_new = $duration_new * 60*60;
            $widget_info->module_info = $module_info;
            $widget_info->planner_version = $var_version;
            $widget_info->tz_apply = $args->tz_apply;
            $widget_info->mini_display_option = $args->mini_display_option;
            $widget_info->mini_date_link = $args->mini_date_link;
            $widget_info->widget_path = $this->widget_path;
            $widget_info->pOption_2 = $ind_pOption_2;
            $widget_info->pTimeSchedule = $dispTimeSchedule;
            $widget_info->pTodaystamp = $pTodaystamp;
            $widget_info->pTimestamp = $pTimestamp;
            $widget_info->dispStart_stamp = $dispStart_stamp;
            $widget_info->dispEnd_stamp = $dispEnd_stamp;
            $widget_info->weekly_base = $ind_weekly_base;
            $widget_info->display_detail = $ind_display_detail;
            $widget_info->display_tooltip = $display_tooltip;
            $widget_info->ind_fill = $ind_fill;
            $widget_info->dft_country_code = $dft_country_code;
            $widget_info->dft_offday = $dft_offday;
            $widget_info->ind_offday_label = $ind_offday_label;
            $widget_info->ind_offday_naNew = $ind_offday_naNew;
            $widget_info->ind_holidayisOffday = $ind_holidayisOffday;
            $widget_info->holiday_country_code = $holiday_country_code;
            $widget_info->country_name_arr = $country_name_arr;
            $widget_info->display_country_select = $display_country_select;
            $widget_info->HolidayByCountry = $HolidayByCountry;
            $widget_info->Memday_arr = $Memday_arr;
            $widget_info->board_country_arr = $board_country_arr;
            $widget_info->board_offday_arr = $board_offday_arr;
            $widget_info->board_offday_option_arr = $board_offday_option_arr;
            $widget_info->jd_today = $jd_today;
            $widget_info->mini_month_option = $args->mini_month_option;
            $widget_info->default_style = $default_style;
			//디버깅을 위해
			//$widget_info->testfld .= date('r',$pTimestamp)." ".date('r',$dispStart_stamp)."-".date('r',$dispEnd_stamp)."<br/>";

            Context::set('widget_info', $widget_info);

            // 템플릿의 스킨 경로를 지정 (skin, colorset에 따른 값을 설정)
            $tpl_path = sprintf('%sskins/%s', $this->widget_path, $args->skin);
            Context::set('colorset', $args->colorset);

            // 템플릿 파일을 지정
            if ($default_style == 'mini') {
            $tpl_file = 'mini_planner';
			}
			else if ($default_style == 'list') {
            $tpl_file = 'calendar_planner_list';
			}
			else if ($default_style == 'simple') {
            $tpl_file = 'calendar_planner_simple';
			}
			else if ($default_style == 'standard' || $default_style == 'calendar') {
            $tpl_file = 'calendar_planner';
			}
			else {
            $tpl_file = 'calendar_planner_simple';
			}

            // 템플릿 컴파일
            $oTemplate = &TemplateHandler::getInstance();
            $output = $oTemplate->compile($tpl_path, $tpl_file);
            return $output;
        }
    }

?>
