<?php
	//include_once '../sys/class/class.db_connect.inc.php';
	
	class Calendar extends DB_Connect
	{
		// 日历当前日期
		private $_useDate;
		// 日历当前月份
		private $_m;
		// 日历当前年份
		private $_y;
		// 当前月有多少天
		private $_daysInMonth;
		// 这个月的起始日期
		private $_startDay;
		/**
		 *
		 */
		public function __construct($dbo=NULL,$useDate=NULL)
		{
			parent::__construct($dbo);

			if( isset($useDate) )
			{
				$this->_useDate = $useDate;
			}
			else
			{
				$this->_useDate = date('Y-m-d H:i:s');
			}

			$ts = strtotime($this->_useDate);
			$this->_m = date('m',$ts);
			$this->_y = date('Y',$ts);		
			
			$this->_daysInMonth = cal_days_in_month(CAL_GREGORIAN, $this->_m, $this->_y);

			$ts = mktime(0,0,0,$this->_m,1,$this->_y);
			$this->_startDay = date('w',$ts);	

		}

		public function _loadEventData($id=NULL)
		{
			$sql = "SELECT 
							event_id,event_title,event_desc,event_start,event_end 
					FROM cal ";

			if(!empty($id))
			{
				$sql .= " WHERE event_id =:id LIMIT 1";   ///warning  不能加 '' !!!!
			}

			else
			{
				$start_ts = mktime(0,0,0,$this->_m,1,$this->_y);
				$end_ts = mktime(0,0,0,$this->_m+1,0,$this->_y);
				$start_date = date('Y-m-d H:i:s',$start_ts);
				$end_date = date('Y-m-d H:i:s',$end_ts);

				$sql .= "WHERE event_start
								BETWEEN '$start_date' and '$end_date'
						ORDER BY event_start";
			}

//$id++;
//echo $id;
			try
			{
				//	
				$stmt = $this->db->prepare($sql);
				if( !empty($id))
				{
					$stmt->bindParam(":id",$id, PDO::PARAM_INT);
				}

		//		echo $sql;

				$stmt->execute();
				$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
				$stmt->closeCursor();

				return $results;
			}catch( Exception $e)
			{
				die ( $e->getMessage() );
			}
		}

		private function _createEventObj()
		{
			$arr = $this->_loadEventData();

			$events =array();

			foreach($arr as $event)
			{
				$day = date('j',strtotime($event['event_start']));

				try
				{
					$events[$day][] = new Event($event);

				}catch( Excetion $e)
				{
					die ($e->getMessage());
				}

			}

			return $events;
		}

		public function buildCalendar()
		{
			$cal_month = date('F Y',strtotime($this->_useDate));

			$weekdays = array('Sun','Mon','Tue','Wed','Thu','Fri','Sat');
			// 给日历添加一个标题
			$html = "\n\t<h2>".$cal_month."</h2>";
			for( $d=0,$labels=NULL; $d<7;++$d)
			{
				$labels .= "\n\t\t<li>". $weekdays[$d] ."</li>";
			}
			$html .= "\n\t<ul class=\"weekdays\">"
					. $labels ."\n\t</ul>";


			//载入活动数据
			$events = $this->_createEventObj();

			//生成日历HTML标记

			$html .="\n\t<ul>";
			for($i=1, $c=1,$t=date('j'),$m=date('m'),$y=date('Y');$c<=$this->_daysInMonth;++$i)
			{
				//为起始日之前的那几天添加class fill

				$class = $i<=$this->_startDay ?  "fill" :NULL;

				//如果当日处理日期是今天，则为它添加class today

				if( $c==$t && $m==$this->_m && $y==$this->_y)
				{
					$class = "today";
				}

				$ls = sprintf("\n\t\t<li class=\"%s\">", $class);
				$le = "\n\t\t</li>";

				//添加日历的主题，也就是该月的每一天
				$event_info = NULL;
				if( $this->_startDay<$i && $this->_daysInMonth>=$c)
				{
					///格式化活动数据
					//$event_info = NULL;
					
					if( isset($events[$c]))
					{
						foreach( $events[$c] as $event)
						{
							$link = '<a href="view.php?event_id='. $event->id .'">' . $event->title .'</a>';
							$event_info .= "\n\t\t\t$link";
						}

					}


					$date = sprintf("\n\t\t\t<strong>%02d</strong>",$c++);
				}
				else{ $date="&nbsp;";}

				//如果赶上周六，就新起一行
				$wrap = $i!=0 && $i%7==0 ? "\n\t</ul>\n\t<ul>" : NULL;

				//将以上的组成一个完整的东西
				$html .= $ls .$date . $event_info . $le .$wrap;
			}
			//为最后几天的添加填充项
			while ($i%7!=1)
			{
				$html .= "\n\t\t<li class=\"fill\">&nbsp;</li>";
				++$i;
			}

			$html .= "\n\t</ul>\n\n";

			return $html;
		}

		private function _loadEventById($id)
		{
			if( empty($id) )
			{
				return NULL;
			}


			$event = $this->_loadEventData($id);

		//	var_dump($event[0]);
			if( isset($event[0]))
			{
				//echo "heere";
				return new Event($event[0]);
			}
			else
			{
				return NULL;
			}
		}

		public function displayEvent($id)
		{
			if( empty($id)){return NULL;}

			$id = preg_replace('/[^0-9]/', '', $id);

			//echo $id;

			$event = $this->_loadEventById($id);

			$ts = strtotime($event->start);
			$date = date('F d ,Y ',$ts);
			$start =date('g:ia',$ts);
			$end = date('g:ia',strtotime($event->end));

		//	var_dump($event);

			return "<h2>$event->title</h2>" 
					. "\n\t<p class=\"dates\">$date,$start&mdash;$end</p>"
					. "\n\t<p>$event->description</p>";
		}

        /**
          *生成一个修改或创建活动的表单
          *
          */

        public function displayForm()
        {
         //  $_POST['event_id']=1;
           

           if( isset($_POST['event_id']))
            {
                $id = (int) $_POST['event_id'];
                //强制类型转换，保证数据安全
            }
            else
            {
                $id = NULL;
            }

            $submit = "Create a New Event";
            //  若传入活动ID 则载入相应的活动数据   
            if(!empty($id))
            {
                $event = $this->_loadEventById($id);

                if(!is_object($event)){return NULL;}

                $submit = "Edit This Event";
            
            
            
            
          //  var_dump($event);
           return <<<HTML
                 <form action="assets/inc/process.inc.php" method="post">
                <fieldset>
                <legend>{$submit}</legend>
                <label for="event_title">Event Title</label>
                <input type="text" name="event_title" id="event_title" value="$event->title" />
                <label for="event_start">Start Time</label>
                <input type="text" name="event_start" id="event_start" value="$event->start"/>
                <label for="event_end">End Time</label>
                <input type="text" name="event_end" id="event_end" value="$event->end"/>
                <label for="event_description">Event Description</label>
                <textarea name="event_description" id="event_description">$event->description</textarea>
                <input type="hidden" name="event_id" value="$event->id"/>
                <input type="hidden" name="token" value="$_SESSION[token]"/>
                <input type="hidden" name="action" value="event_edit"/>
                <input type="submit" name="event_submit" value="$submit"/>
                or <a href="./">cancel</a>
               </fieldset>
               </form>
HTML;
     
         /** <<<HTML 起始这一行后面不能有空格
           *  HTML 结束这一行必须没有任何其他字符，除了最后的；  也不能缩进
           *这是文档句法 又称为定界符号
           *
           */
            } ///这里书上写的不好。。。。没有保证如果不传$id 则会warning的！！
        }



	}
?>
