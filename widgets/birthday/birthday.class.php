<?php
    /**
     * @class birthday
     * @author 난다날아 (sinsy200@gmail.com)
     * @brief 생일 출력 위젯
     **/

    class birthday extends WidgetHandler {

        /**
         * @brief 위젯의 실행 부분
         *
         * ./widgets/위젯/conf/info.xml 에 선언한 extra_vars를 args로 받는다
         * 결과를 만든후 print가 아니라 return 해주어야 한다
         **/
        function proc($args) {
            $D = false;
            
            if ($D){ debugPrint($args); }
            
            Context::set('colorset', $args->colorset);
            Context::set('widget_info', $widget_info);
            Context::set('args', $args);
            
            // 음력 사용 중인지 체크
            $oLunarModel = &getModel('lunar');
            if ($oLunarModel)   $config = $oLunarModel->getConfig();
            if ($config)    $use_lunar_birthday = $config->use_lunar_birthday;
            
            // 날짜 범위
            if (empty($args->gap))  $args->gap = 0;
            $gap = (int)$args->gap;
            if ($gap < 0)   $gap = 0;
            
            // 생일을 구할 날짜 범위를 계산
            $today = time();
            $start_day = $today - $gap * 60 * 60 * 24;
            $end_day = $today + $gap * 60 * 60 * 24;
            $days = 1 + $gap * 2;
            
            // 양력 날짜 범위
            // $start_solar_day = date("md", $start_day);
            // $end_solar_day = date("md", $end_day);
            
            if ($D){
                debugPrint('start_solar_day: ' . date("md", $start_day));
                debugPrint('end_solar_day: ' . date("md", $end_day));
            }
            
            // 각 날짜의 생일자 구하기
            for ($i = 0, $j = -1 * $gap; $i < $days; $i++, $j++){
                unset($query_args);
                
                // 양력 생일
                $solar = $start_day + $i * 60 * 60 * 24;
                $solar_day = date("md", $solar);
                $query_args->solar_day = $solar_day;
                    
                // 음력을 사용하지 않을 때
                if ($use_lunar_birthday != "Y"){
                    $output = executeQuery('widgets.birthday.getBirthdayList', $query_args);
                    $member_list = $output->data;
                    
                    if ($D){ debugPrint($output); }
                }
                
                // 음력을 사용할 때
                else{
                    $lunar = $oLunarModel->tolunar(date("Y", $solar), date("m", $solar), date("d", $solar));
                    $lunar_day = sprintf("%02d%02d", $lunar[1][1], $lunar[1][2]);
                    $query_args->lunar_day = $lunar_day;
                    // if ($lunar[1][3])
                        // $query_args->leap = 'Y';
                    // else
                        // $query_args->leap = 'N';
                    
                    $output = executeQuery('widgets.birthday.getLunarBirthdayList', $query_args);
                    $member_list = $output->data;
                    
                    if ($D){ debugPrint($output); }
                }
                
                if ($member_list){
                    if (!is_array($member_list)) $member_list = array($member_list);
                
                    if (count($member_list)){
                        $birthday[$j]->member_list = $member_list;
                        $birthday[$j]->solar = date("m-d", $solar);
                        $birthday[$j]->lunar = $lunar[1][1] . '-' . $lunar[1][2];
                        $birthday[$j]->leap = $lunar[1][3];
                    }
                }
            }
            
            if ($D){ debugPrint($birthday); }
            
            Context::loadLang(sprintf('%slang', $this->widget_path));
            Context::set('birthday', $birthday);

            // 템플릿 컴파일
            $tpl_path = sprintf('%sskins/%s', $this->widget_path, $args->skin);
            $tpl_file = 'birthday';

            $oTemplate = &TemplateHandler::getInstance();
            return $oTemplate->compile($tpl_path, $tpl_file);
        }
    }
?>
