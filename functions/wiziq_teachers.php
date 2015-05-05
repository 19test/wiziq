<?php
/*
 * This class contains teachers functions
 * @since 1.1
 */ 

class Wiziq_Teachers {
	
	
	/*
	 * Function to redirect to a particular page 
	 * pass url of the page to redirect to
	 * @since 1.1
	 */ 
	function wiziq_teacher_url_redirect ( $wiziq_redirect_url ) {
		?>
			<script>
				window.location = "<?php echo $wiziq_redirect_url ; ?>";
			</script>
		<?php
	} // end redirect function 
	
	
	/*
	 * Function to get a details of a single teacher
	 * pass teacher id
	 * @since 1.1
	 */ 
	function wiziq_get_teacher ( $teacher_id ) {
		global $wpdb;
		$wiziq_teacher = $wpdb->prefix."wiziq_teacher";
		$qry = "select * from $wiziq_teacher where id = '$teacher_id'" ;
		$wiziq_results = $wpdb->get_row( $qry );
		if ( !empty($wiziq_results) ) {
			return $wiziq_results;
		} else {
			return false;
		}
	}
	
	
	/*
	 * Function to get a details of a single teacher
	 * pass user id, id generated by wordpress on registration
	 * @since 1.1
	 */ 
	function wiziq_get_teacher_by_userid ( $user_id ) {
		global $wpdb;
		$wiziq_teacher = $wpdb->prefix."wiziq_teacher";
		$qry = "select * from $wiziq_teacher where puser_id = '$user_id'" ;
		$wiziq_results = $wpdb->get_row( $qry );
		if ( !empty($wiziq_results) ) {
			return $wiziq_results;
		} else {
			return false;
		}
	}
	
	
	/*
	 * @since 1.1
	 * Get no of classes of a teacher
	 */ 
	function wiziq_get_teacher_upcoming_classes ( $teacher_id ) {
		global $wpdb;
		$wiziq_classes = $wpdb->prefix."wiziq_wclasses";
		$qry = "select count(id) from $wiziq_classes where created_by = '$teacher_id' and status = 'upcoming' ";
		$wiziq_results = $wpdb->get_var( $qry );
		if ( !empty($wiziq_results) ) {
			return $wiziq_results;
		} else {
			return false;
		}
	}
	
	/*
	 * Get list of classes of a teacher
	 * @since 1.1
	 */ 
	function wiziq_get_teacher_upcoming_classes_list ( $teacher_id ) {
		global $wpdb;
		$wiziq_classes = $wpdb->prefix."wiziq_wclasses";
		$qry = "select * from $wiziq_classes where created_by = '$teacher_id' and status = 'upcoming' ";
		$wiziq_results = $wpdb->get_results( $qry );
		if ( !empty($wiziq_results) ) {
			return $wiziq_results;
		} else {
			return false;
		}
	}
	 
	
	/*
	 * Check if deactivated teachers all upcoming classes are deleted
	 * @since 1.1
	 */ 
	function wiziq_teachers_deactivated () {
		global $wpdb;
		$wiziq_teacher = $wpdb->prefix."wiziq_teacher";
		$wiziq_classes = $wpdb->prefix."wiziq_wclasses";
		$qry = "select * from $wiziq_teacher where is_active = '0' and deactivated = '1' " ;
		$res = $wpdb->get_results( $qry );
		if ( !empty ( $res ) ) {
			foreach ( $res as $r ) {
				$list = $this->wiziq_get_teacher_upcoming_classes_list ( $r->puser_id );
				if ( $list )  {
					$wiziq_classes->wiziq_delete_single_class_teacher ( $list->id );
				} else {
					$qry = "update $wiziq_teacher set deactivated = '0' where puser_id = '$r->puser_id' ";
					$res = $wpdb->query( $qry );
				}
			}
		}
	}
	
	/**
	 * Get teachers enrolled in a course
	 * @course_id 
	 */
	function wiziq_get_teachers_in_course ( $course_id ) {
		global $wpdb;
		$wiziq_teacher = $wpdb->prefix."wiziq_teacher";
		$wiziq_enrolled_teachers = $wpdb->prefix . "wiziq_enroluser";
		$qry = "select * from $wiziq_teacher where is_active = '1' and can_schedule_class = '1' " ;
		$wiziq_results = $wpdb->get_results( $qry );
		if ( !empty($wiziq_results) ) {
			/*
			 * create list of teachers
			 */ 
			foreach ( $wiziq_results as $teachers ) {
				if ( !isset ( $user_list ) ) {
					$user_list = $teachers->puser_id;
				} else {
					$user_list .= ','.$teachers->puser_id;
				}
			}
			/*
			 * get teachers enrolled in a course and return
			 */ 
			$enrolled_teacher_qry = "select * from $wiziq_enrolled_teachers where course_id  = '$course_id' and create_class = '1' and user_id IN ( $user_list ) ";
			$enrolled_teacher_res = $wpdb->get_results( $enrolled_teacher_qry );
			if ( ! empty ( $enrolled_teacher_res ) ) {
				return $enrolled_teacher_res;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}//end  of get teachers in a course 
	 
	  
	/*
	 * Function to get all the teachers in sorted way
	 * Pass 1 if paginted result required else pass 0
	 * @since 1.1
	 */ 
	function wiziq_get_teacher_sorted ( $pagination ,$start, $limit , $sortby , $orderby ) {
		global $wpdb;
		$wiziq_teacher = $wpdb->prefix."wiziq_teacher";
		if ( $pagination && $sortby && $orderby) {
			$qry = "select * from $wiziq_teacher order by $sortby $orderby LIMIT $start ,$limit" ;
			
		} else {
			$qry = "select * from $wiziq_teacher order by id DESC" ;
		}
		$wiziq_results = $wpdb->get_results( $qry );
		if ( !empty($wiziq_results) ) {
			return $wiziq_results;
		} else {
			return false;
		}
	}// end function to get classes result for a course
	
	/*
	 * Function to view teachers
	 * @since 1.1
	 */
	function wiziq_view_teachers() {
		
		global $wpdb;
		/*
		 * Sorting functionality
		 * 
		 */
		if ( isset ( $_GET['sort-by'] ) && isset ($_GET['order-by']) ) {
			$wiziq_teacher = $wpdb->prefix."wiziq_teacher";
			if ( $wiziq_Util->wiziq_table_exist_check( $wiziq_teacher ,$_GET['sort-by'] ) && ('desc' == $_GET['order-by'] || 'asc' == $_GET['order-by']) ) {
				$sortby = $_GET['sort-by'];
				$orderby = $_GET['order-by'];
			}else {
				$sortby = "id";
				$orderby = "desc";
			}
		} else {
			$sortby = "id";
			$orderby = "desc";
		}
		$teacher_result = $this->wiziq_get_teacher_sorted ( 0 , 0, 0 , 0 , 0 );
		$wiziq_Util = new Wiziq_Util;
		$total_pages = !empty($teacher_result)?count($teacher_result):0 ;
		$limit = WIZIQ_PAGINATION_LIMIT;
		$adjacents = 3;
		$page = isset($_GET['pageno'])?$_GET['pageno']:'';
		if($page) 
			$start = ($page - 1) * $limit; 
		else
			$start = 0;
		$targetpage = "?page=wiziq_teachers&";
		$pagination =  $wiziq_Util->custom_pagination($page,$total_pages,$limit,$adjacents,$targetpage);
		
		$result  = $this->wiziq_get_teacher_sorted ( '1' , $start , $limit , $sortby , $orderby  );
		
		$countrow = 0;
		?>
		<h2><?php _e('WizIQ Teachers', 'wiziq'); ?><a class = "add-new-h2"  href= "<?php echo WIZIQ_TEACHER_MENU; ?>&add_teacher" ><?php _e('Add Teachers', 'wiziq'); ?></a></h2>
		<?php
			if (isset ($_GET['esuccess']) ) {
				echo '<div class = "updated" ><p><strong>'.__('Teacher updated succesffully','wiziq').'</strong></p></div>';
			} elseif ( isset ($_GET['success']) ) {
				echo '<div class = "updated" ><p><strong>'.__('Teacher added succesffully','wiziq').'</strong></p></div>';
			} 
		?>
		<form method = "post" >
			<table class= "wp-list-table widefat fixed pages" >
				<thead>
					<tr>
						<th class = "manage-column column-cb check-column" >
							<label class="screen-reader-text" for="cb-select-all-1"><?php _e('Select All','wiziq'); ?></label>
							<input id="cb-select-all-1" type="checkbox">
						</th>
						<th class = "manage-column" >
							<?php _e('Teacher Name', 'wiziq'); ?>
						</th>
						<th class = "manage-column" >
							<?php _e('Teacher Email', 'wiziq'); ?>
						</th>
						<th class = "manage-column" >
							<?php _e('Teacher Password', 'wiziq'); ?>
						</th>
						<th class = "manage-column" >
							<?php _e('Can Schedule Class', 'wiziq'); ?>
						</th>
						<th class = "manage-column" >
							<?php _e('Activate', 'wiziq'); ?>
						</th>
						<th class = "manage-column" >
							<?php _e('Manage Teacher', 'wiziq'); ?>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th class = "manage-column column-cb check-column" >
							<label class="screen-reader-text" for="cb-select-all-1"><?php _e('Select All','wiziq'); ?></label>
							<input id="cb-select-all-1" type="checkbox">
						</th>
						<th class = "manage-column" >
							<?php _e('Teacher Name', 'wiziq'); ?>
						</th>
						<th class = "manage-column" >
							<?php _e('Teacher Email', 'wiziq'); ?>
						</th>
						<th class = "manage-column" >
							<?php _e('Teacher Password', 'wiziq'); ?>
						</th>
						<th class = "manage-column" >
							<?php _e('Can Schedule Class', 'wiziq'); ?>
						</th>
						<th class = "manage-column" >
							<?php _e('Activate', 'wiziq'); ?>
						</th>
						<th class = "manage-column" >
							<?php _e('Manage Teacher', 'wiziq'); ?>
						</th>
					</tr>
				</tfoot>
				<tbody>
					<?php
						
					?>
					<?php 
					if ( $result ) :
						foreach ( $result as $res ) :
							$countrow++;
							if( "1" == $countrow) 
							{ 
								$row_class = "alternate iedit cclass";
							} 
							else 
							{
								$countrow = "0";
								$row_class ="iedit cclass" ;
							}
							$nonce = wp_create_nonce( 'edit-teacher-' . $res->id );
							$edit_url = WIZIQ_TEACHER_MENU.'&edit_teacher&teacher_id='.$res->id.'&wp_nonce='.$nonce;
						?>
						<tr id = "tclass-<?php echo $res->id; ?>" class = "<?php echo $row_class; ?>" >
							<th class="check-column" scope="row">
								<label class="screen-reader-text" >Select <?php echo $res->id; ?></label>
								<input id="cb-select-<?php echo $res->id; ?>" type="checkbox" value="<?php echo $res->id; ?>" name = "class-checkbox[]" value= "<?php echo $res->id; ?>">
								<div class="locked-indicator"></div>
							</th>
							<td>
								<?php 
								$user_info = get_userdata( $res->puser_id  );
								if(!empty($user_info->first_name)){
									$teacher_name = $user_info->first_name.' '.$user_info->last_name;
								} else	{
									$teacher_name = $user_info->display_name;
								}
								echo $teacher_name; 
								?>
								<div class="row-actions">
									<span class="edit">
										<a title="<?php _e('Edit this teacher', 'wiziq'); ?>" href="<?php echo $edit_url; ?>"><?php _e( 'Edit' , 'wiziq' ); ?></a>
									</span>
								</div>
							</td>
							<td><?php echo $res->teacher_email; ?></td>
							<td>
								<div id="teacher_pass_<?php echo $res->id;?>" class = "wiziq_hide" >
									<?php echo $res->password; ?>
								</div>
								<div id= "dummy_pass_<?php echo $res->id;?>">**********</div>
								<span class= "teacher_password" id= "pass_<?php echo $res->id; ?>"><?php _e('View','wiziq');?></span>
							</td>
							<td>
								<?php 
									$schedule = $res->can_schedule_class ; 
									if ( $schedule ) 
										_e('Yes', 'wiziq' );
									else 
										_e ('No','wiziq');
								?>
							</td>
							<td>
								<?php 
									$active = $res->is_active ; 
									if ( $active ) 
										_e('Yes', 'wiziq' );
									else 
										_e ('No','wiziq');
								?>
							</td>
							<td>
								<a href="<?php echo $edit_url; ?>">
									<img title= "<?php _e('Edit this teacher', 'wiziq'); ?>" class = "classes-images" src= "<?php echo WIZIQ_PLUGINURL_PATH.'images/edit20.png'; ?>" alt ="<?php _e('Edit','wiziq'); ?>" />
								</a>
							</td>
						</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr class = "alternate iedit cclass" >
							<td>&nbsp;</td>
							<td colspan = "6" ><?php _e('No teacher added','wiziq');?></td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>
			<div class= "tablenav bottom">
				<?php 
				if ($result )
					echo $pagination;
				?>
			</div>
			<div class = "wiziq_hide" >
				<span id = "wiziq_are_u_sure" ><?php _e('Are you sure, you want to delete','wiziq');?></span>
				<span id = "wiziq_teacher_sure" ><?php _e('This teacher have upcoming classes. Are you sure, you want to delete','wiziq');?></span>
			</div>
		</form>
		<?php
	}//end function to view classes 
	
	/*
	 * Function to display add teacher form
	 * @since 1.1
	 */
	function wiziq_add_teacher_form()   {
		$wiziq_api_functions = new wiziq_api_functions;
		$teacher_result = $this->wiziq_get_teacher_sorted ( 0 , 0, 0 , 0 , 0 );
		$teachers[] = "";
		if (!empty ($teacher_result)) {
			foreach ($teacher_result as $teacher_res){ 
				$teachers[]=$teacher_res->puser_id;
			}
		}
		?>
		<h3><?php _e('Add Teacher','wiziq'); ?></h3>
		<?php 
			//display if any errors
			global $myerror;
			if ( is_wp_error( $myerror ) ) {
				$teacher_error = $myerror->get_error_message('wiziq_teacher_create_error');
				if ( $teacher_error ) {
					echo $teacher_error;
				}
			} 
			/*
			* In case of error display all the fields as they were
			*/ 
			if ( isset ($_POST['wiziq_add_teacher']) ) {
				$teacher_data = $_POST;
			} else {
				$teacher_data = false;
			}
		
		$users = get_users(array('exclude' => $teachers));
		$totalteach = 0;
		if ( ! empty ( $users )) {
			foreach ($users as $user) {
					// removed on 24 nov 2014 Author and Editor check
					//~ $user_info = get_userdata( $user->ID  );
					//~ $user_rle = implode(', ', $user_info->roles);
					//~ if ( $user_rle == 'author' || $user_rle == 'editor' || $user_rle == 'administrator') {
						$totalteach++;
					//}
			}
		}
		
		if ( $totalteach == 0 ) :
			echo '<h4>No user to add as teacher</h4>';
		else: 
				
		?>
		
		<form method = "post" id = "add_teacher_form" >
			<?php wp_nonce_field('add_teacher','add_teacher_nonce'); ?>
				<table class = "form-table" >
				<tbody>
					<tr>
						<th><?php _e('Select user', 'wiziq'); ?><span class="description"> (<?php _e('required', 'wiziq' ); ?>)</span></th>
						<td>
							<?php
							
							?>
							<select name="puser_id" class="wiziq-select" id="wiziq-teacher">
								<?php
								foreach ($users as $user) {
									$user_info = get_userdata( $user->ID  );
									$user_rle = implode(', ', $user_info->roles);
							// removed on 24 nov 2014 Author and Editor check  ( comment start if condition )
									//if ( $user_rle == 'author' || $user_rle == 'editor' || $user_rle == 'administrator') {
										if(!empty($user->first_name)){
											$teacher_name = $user->first_name.' '.$user->last_name;
										} else	{
											$teacher_name = $user->display_name;
										}
										echo '<option value="' . $user->ID . '" >' . $teacher_name . '</option>';				// removed on 24 nov 2014 Author and Editor check ( comment closed if condition )
									//}
								}
								?>
							</select>
							<div class = "wiziq_error" id = "teacher_name_err" ></div>
						</td>
					</tr>
					<tr>
						<th><?php _e('Password', 'wiziq'); ?><span class="description"> (<?php _e('required', 'wiziq' ); ?>)</span></th>
						<td>
							<input maxlength= "70" type = "password" class = "regular-text" id = "password" name="password" />
							<div class = "wiziq_error" id = "teacher_password_err" ></div>
						</td>
					</tr>
			
					<tr>
						<th><?php _e('Time zone', 'wiziq'); ?></th>
						<td>
							<select id="class_timezone" name="classtimezone">
							<?php
							/*
							 * Get time zones from api
							 */  
							$timezone = $wiziq_api_functions->getTimeZone();
							foreach ($timezone as $key => $values) {
							?>
								<option value = "<?php echo $key; ?>"  <?php if(isset ($_POST['classtimezone'] ) && $_POST['classtimezone'] == $key ) echo ' selected'; ?> ><?php echo $values; ?></option>
							<?php 
							}
							?>
							</select>
							<div class = "wiziq_error" id = "teacher_phone_err" ></div>
						</td>
					</tr>
					<tr>
						<th><?php _e('Can schedule class', 'wiziq'); ?></th>
						<td>
							<input type="radio" name = "can_schedule_class" checked = "checked" class= "teacher_class_schedule"  value = "1"    ><?php _e('Yes', 'wiziq'); ?>
							<input type="radio" name = "can_schedule_class" class= "teacher_class_schedule"  value = "0"    ><?php _e('No', 'wiziq'); ?>
						</td>
					</tr>
					<tr>
						<th><?php _e('Active', 'wiziq'); ?></th>
						<td>
							<input type="radio" name = "is_active" checked = "checked" class= "teacher_active"  value = "1"  ><?php _e('Yes', 'wiziq'); ?>
							<input type="radio" name = "is_active" class= "teacher_active"  value = "0"  ><?php _e('No', 'wiziq'); ?>
						</td>
					</tr>
					<tr>
						<th><?php _e('About the teacher', 'wiziq'); ?></th>
						<td>
							<textarea maxlength= "1000" id="teacher_descirption" cols="40" rows="5" name = "about_the_teacher"><?php if ($teacher_data) echo $teacher_data['about_the_teacher'];?></textarea>
							<div class= "wiziq_limit wiziq_description"><?php _e('You can enter upto 1000 characters.','wiziq');?></div>
						</td>
					</tr>
				</tbody>
			</table>
			<input type = "hidden" name = "upcoming_classes" id = "upcoming_classes" value = "0" />
			<input class= "button button-primary wiziq-button" id = "wiziq_add_teacher" type = "Submit" name = "wiziq_add_teacher" value="<?php _e('Save','wiziq') ?>" /> 
			<a class= "button button-primary wiziq-button" id = "wiziq_cancel_course" href = "<?php echo WIZIQ_TEACHER_MENU; ?>" ><?php _e('Cancel','wiziq') ?></a>
		</form>
			<div class= "wiziq_hide">
				<div  id = "teacher_password_empty" ><?php _e('Please enter password.', 'wiziq'); ?></div>
				<div  id = "teacher_password_length" ><?php _e('Password length between 6 - 15 character.', 'wiziq'); ?></div>
			</div>
		<?php
		endif;
	}
	
	/*
	 * Function to add a teacher
	 * @since 1.1
	 */ 
	function wiziq_add_teacher($data) {
		
		global $wpdb;
		global $current_user;
		$wiziq_api_functions  = new wiziq_api_functions ;
		$wiziq_teacher = $wpdb->prefix."wiziq_teacher";
		/*
		 * Create request parameters for teacher
		 */
		
		$method = "add_teacher";
		$user_info = get_userdata( $data['puser_id']  );
		$requestparameters["teacher_id"] = $user_info->ID;
		if(!empty($userdata->user_firstname)){
			$requestparameters["name"] = $user_info->first_name.' '.$user_info->last_name;
		} else	{
			$requestparameters["name"] = $user_info->display_name;
		}
        $requestparameters["email"]               = $user_info->user_email;
        $requestparameters["password"]            = $data['password'];
        $requestparameters["timezone"]            = $data['classtimezone'];
       
		$requestparameters["can_schedule_class"]  = $data['can_schedule_class'];
		foreach ( $user_info->roles as $role) {
			if ($role == 'administrator' )
				$requestparameters["can_schedule_class"]  = '1';
		}
		
		
		$deactivated = $data['is_active'];
		$requestparameters["is_active"] = $data['is_active'];
		foreach ( $user_info->roles as $role) {
			if ($role == 'administrator' )
				$requestparameters["is_active"]  = '1';
		}
        $requestparameters["about_the_teacher"]   = $data['about_the_teacher'];
		
		try {
			//call to api method 
			$add_teacherxml = $wiziq_api_functions->wiziq_teacher_method($requestparameters , $method );
			$add_teacherxmlstatus = $add_teacherxml->attributes();
			if ($add_teacherxmlstatus == 'ok') {
				$response['teacher_id'] = (string)$add_teacherxml->$method->teacher_id;
				$insqry = "insert into $wiziq_teacher 
				( puser_id, password, timezone, 
				about_the_teacher , can_schedule_class , is_active, 
				teacher_id, teacher_email, deactivated )
				values ('$user_info->ID','".$requestparameters["password"]."','".$requestparameters["timezone"]."',
				'".$requestparameters["about_the_teacher"]."','".$requestparameters["can_schedule_class"]."',
				'".$requestparameters["is_active"]."',
				'".$response['teacher_id']."','$user_info->user_email',
				'".$deactivated."'
				)
				";
				$wpdb->query($insqry);
				$this->wiziq_teacher_url_redirect(WIZIQ_TEACHER_MENU."&success");
			} else {
				$errorcode = $add_teacherxml->error->attributes()->code;
				$errormsg = $add_teacherxml->error->attributes()->msg;
				if ("1057" == $errorcode ||"1058" == $errorcode || "1059" == $errorcode || "1060" == $errorcode || "1061" == $errorcode || "1062" == $errorcode || "1063" == $errorcode || "1064" == $errorcode || "1065" == $errorcode || "1066" == $errorcode || "1067" == $errorcode ) {
					$error1 =  eval('return WIZIQ_COM_'. $errorcode . ';');
				}
				else {
					$error1 = WIZIQ_COM_CONTENT_ERROR;
				}
				$cancel_erro = '<div class="error"><p><strong>ERROR </strong>'.$error1.'</p></div>';
				global $myerror;
				$myerror = new WP_Error( 'wiziq_teacher_create_error', $cancel_erro ); 
				return false;
			}
		}
		catch ( Exception $ex ){
			$cancel_erro = '<div class="error"><p><strong>'.__('ERROR','wiziq').' '.'</strong>'. __(WIZIQ_COM_CATCH,'wiziq').'</p></div>';
			global $myerror;
			$myerror = new WP_Error( 'wiziq_teacher_create_error', $cancel_erro ); 
			return false;
		}
	}//end function to add the teacher
	
	/*
	 * Function to display edit form to edit the teacher
	 * pass teacher id to display the form
	 * pass nonce to verify the valid request
	 * @since 1.1
	 */  
	 function wiziq_edit_teacher_form ( $teacher_id, $nonce ) {
		 /*
		  * verify the nonce
		  */ 
		if ( ! wp_verify_nonce( $nonce , 'edit-teacher-'.$teacher_id  ) ) {
			$this->wiziq_teacher_url_redirect(WIZIQ_TEACHER_MENU);
		} 
		$wiziq_api_functions = new wiziq_api_functions;
		$teacher_data = $this->wiziq_get_teacher ( $teacher_id );
		
		//display if any errors
		global $myerror;
		if ( is_wp_error( $myerror ) ) {
			$teacher_error = $myerror->get_error_message('wiziq_teacher_create_error');
			if ( $teacher_error ) {
				echo $teacher_error;
			}
		}
		
		/*
		 * get upcoming classes of teacher
		 */ 
		$upcoming_classes = $this->wiziq_get_teacher_upcoming_classes( $teacher_data->puser_id );
		
			
		?>
			<h3><?php _e('Edit Teacher','wiziq'); ?></h3>
			<form method = "post" id = "add_teacher_form" >
			<?php wp_nonce_field('add_teacher','add_teacher_nonce'); ?>
				<table class = "form-table" >
				<tbody>
					<tr>
						<th><?php _e('Teacher name', 'wiziq'); ?><span class="description"> (<?php _e('required', 'wiziq' ); ?>)</span></th>
						<td>
							<?php
							$user_info = get_userdata( $teacher_data->puser_id  );
							if(!empty($user_info->first_name)){
								$teacher_name = $user_info->first_name.' '.$user_info->last_name;
							} else	{
								$teacher_name = $user_info->display_name;
							}
							echo $teacher_name; 
							?>
							<input type="hidden" name = "puser_id" value="<?php echo $teacher_data->puser_id; ?>" />
							<input type="hidden" name = "teacher_id" value="<?php echo $teacher_data->teacher_id; ?>" />
						</td>
					</tr>
					<tr>
						<th><?php _e('Password', 'wiziq'); ?><span class="description"> (<?php _e('required', 'wiziq' ); ?>)</span></th>
						<td>
							<input maxlength= "70" type = "password" class = "regular-text" id = "password" name="password"  value = "<?php echo $teacher_data->password; ?>"/>
							<div class = "wiziq_error" id = "teacher_password_err" ></div>
						</td>
					</tr>
					<tr>
						<th><?php _e('Time zone', 'wiziq'); ?></th>
						<td>
							<select id="class_timezone" name="classtimezone">
							<?php 
							$timezone = $wiziq_api_functions->getTimeZone();
							foreach ($timezone as $key => $values) {
								echo  $key;
								?>
									<option value = "<?php echo $key; ?>"  <?php if( $teacher_data->timezone == $key ) echo ' selected'; ?> ><?php echo $values; ?></option>
								<?php 
							}
							?>
							</select>
							<div class = "wiziq_error" id = "teacher_phone_err" ></div>
						</td>
					</tr>
					<tr>
						<th><?php _e('Can schedule class', 'wiziq'); ?></th>
						<td>
							<input type="radio" name = "can_schedule_class" <?php if ( "1" == $teacher_data->can_schedule_class ) echo 'checked = "checked"' ?> class= "teacher_class_schedule"  value = "1"    ><?php _e('Yes', 'wiziq'); ?>
							<input type="radio" name = "can_schedule_class" <?php if ( "0" == $teacher_data->can_schedule_class ) echo 'checked = "checked"' ?> class= "teacher_class_schedule"  value = "0"    ><?php _e('No', 'wiziq'); ?>
						</td>
					</tr>
					<tr>
						<th><?php _e('Active', 'wiziq'); ?></th>
						<td>
							<input type="radio" name = "is_active" <?php if ( "1" == $teacher_data->is_active ) echo 'checked = "checked"' ?> class= "teacher_active"  value = "1"  ><?php _e('Yes', 'wiziq'); ?>
							<input type="radio" name = "is_active" <?php if ( "0" == $teacher_data->is_active ) echo 'checked = "checked"' ?> class= "teacher_active"  value = "0"  ><?php _e('No', 'wiziq'); ?>
						</td>
					</tr>
					<tr>
						<th><?php _e('About the teacher', 'wiziq'); ?></th>
						<td>
							<textarea maxlength= "1000" id="teacher_descirption" cols="40" rows="5" name = "about_the_teacher"><?php if ( $teacher_data ) echo $teacher_data->about_the_teacher;?></textarea>
							<div class= "wiziq_limit wiziq_description"><?php _e('You can enter upto 1000 characters.','wiziq');?></div>
						</td>
					</tr>
				</tbody>
			</table>
			<input type = "hidden" name = "upcoming_classes" id = "upcoming_classes" value = "<?php echo $upcoming_classes; ?>" />
			<input class= "button button-primary wiziq-button" id = "wiziq_add_teacher" type = "Submit" name = "wiziq_edit_teacher" value="<?php _e('Save','wiziq') ?>" /> 
			<a class= "button button-primary wiziq-button" id = "wiziq_cancel_course" href = "<?php echo WIZIQ_TEACHER_MENU; ?>" ><?php _e('Cancel','wiziq') ?></a>
		</form>
		<div class= "wiziq_hide">
			<div  id = "teacher_password_empty" ><?php _e('Please enter password.', 'wiziq'); ?></div>
			<div  id = "teacher_password_length" ><?php _e('Password length between 6 - 15 character.', 'wiziq'); ?></div>
			<span id = "wiziq_teacher_sure" ><?php _e('This teacher have upcoming classes. Are you sure, you want to delete','wiziq');?></span>
		</div>
		<?php
	 }//end function to display the edit teacher form
	 
	 /*
	  * Function to update the teacher
	  * pass post array, teacher id and nonce
	  * @since 1.1
	  */ 
	 function wiziq_edit_teacher ( $data, $teacher_id, $nonce ) {
		/*
		 * verify the nonce
		 */ 
		if ( ! wp_verify_nonce( $nonce , 'edit-teacher-'.$teacher_id  ) ) {
			$this->wiziq_teacher_url_redirect(WIZIQ_TEACHER_MENU);
		}
		global $wpdb;
		global $current_user;
		$wiziq_api_functions  = new wiziq_api_functions ;
		$wiziq_classes  = new Wiziq_Classes;
		$wiziq_teacher = $wpdb->prefix."wiziq_teacher";
		/*
		 * Create request parameters for edit teacher
		 */
		$method = "edit_teacher";
		$user_info = get_userdata( $data['puser_id']  );
		$requestparameters["teacher_id"] = $data['teacher_id'];
		if(!empty($userdata->user_firstname)){
			$requestparameters["name"] = $user_info->first_name.' '.$user_info->last_name;
		} else	{
			$requestparameters["name"] = $user_info->display_name;
		}
		
		$requestparameters["email"]               = $user_info->user_email;
		$requestparameters["password"]            = $data['password'];
		$requestparameters["timezone"]            = $data['classtimezone'];
		
		$requestparameters["can_schedule_class"]  = $data['can_schedule_class'];
		foreach ( $user_info->roles as $role) {
			if ($role == 'administrator' )
				$requestparameters["can_schedule_class"]  = '1';
		}
		
		$requestparameters["is_active"]           = $data['is_active'];
		foreach ( $user_info->roles as $role) {
			if ($role == 'administrator' )
				$requestparameters["is_active"]  = '1';
		}
		$requestparameters["about_the_teacher"]   = $data['about_the_teacher'];
		try {
			//call to api method 
			$add_teacherxml = $wiziq_api_functions->wiziq_teacher_method($requestparameters , $method );
			$add_teacherxmlstatus = $add_teacherxml->attributes();
			if ($add_teacherxmlstatus == 'ok') {
				$response['teacher_id'] = (string)$add_teacherxml->$method->teacher_id;
				$insqry = "update $wiziq_teacher 
				set password = '".$requestparameters["password"]."', 
				timezone = '".$requestparameters["timezone"]."' , 
				about_the_teacher = '".$requestparameters["about_the_teacher"]."' , 
				can_schedule_class = '".$requestparameters["can_schedule_class"]."', 
				is_active = '".$requestparameters["is_active"]."'
				where id = '$teacher_id'";
				$wpdb->query($insqry);
				
				/*
				 * delete teachers form upcoming classes
				 */ 
				if ('0' == $requestparameters['is_active'] || '0' == $requestparameters['can_schedule_class'] ) {
					$selectqry = "select puser_id from $wiziq_teacher where id = '$teacher_id' ";
					$res = $wpdb->get_row($selectqry);
					$delete_enroll = $wpdb->prefix . "wiziq_enroluser";
					$deletefromenrolltable= "update $delete_enroll set create_class = '0', upload_content  = '0' where user_id = '$res->puser_id'";
					$wpdb->query($deletefromenrolltable);
				}
				/*
				 * delete teachers classes if teacher is deactivated
				 */ 
				if ('0' == $requestparameters['is_active'] ) {
					$selectqry = "select puser_id from $wiziq_teacher where id = '$teacher_id' ";
					$res = $wpdb->get_row($selectqry);
					$classes_list = $this->wiziq_get_teacher_upcoming_classes_list ( $res->puser_id );
					if ( $classes_list ) {
						foreach ( $classes_list as $list ) {
							$wiziq_classes->wiziq_delete_single_class_teacher ( $list->id );
						}
					}
				}
				if ('1' == $requestparameters['is_active'] ) { 
					$qry = "update $wiziq_teacher set deactivated = '1' where id = '$teacher_id' ";
					$res = $wpdb->query( $qry );
				}
				$this->wiziq_teacher_url_redirect(WIZIQ_TEACHER_MENU."&esuccess");
			} else {
				$errorcode = $add_teacherxml->error->attributes()->code;
				$errormsg = $add_teacherxml->error->attributes()->msg;
				if ("1057" == $errorcode ||"1058" == $errorcode || "1059" == $errorcode || "1060" == $errorcode || "1061" == $errorcode || "1062" == $errorcode || "1063" == $errorcode || "1064" == $errorcode || "1065" == $errorcode || "1066" == $errorcode || "1067" == $errorcode ) {
					$error1 =  eval('return WIZIQ_COM_'. $errorcode . ';');
				}
				else {
					$error1 = WIZIQ_COM_CONTENT_ERROR;
				}
				$cancel_erro = '<div class="error"><p><strong>ERROR </strong>'.$error1.'</p></div>';
				global $myerror;
				$myerror = new WP_Error( 'wiziq_teacher_create_error', $cancel_erro ); 
				return false;
			}
		}
		catch ( Exception $ex ){
			$cancel_erro = '<div class="error"><p><strong>'.__('ERROR','wiziq').' '.'</strong>'. __(WIZIQ_COM_CATCH,'wiziq').'</p></div>';
			global $myerror;
			$myerror = new WP_Error( 'wiziq_teacher_create_error', $cancel_erro ); 
			return false;
		}
	 }// end function to edit the teacher
	
}
