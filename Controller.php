<?php 	namespace App;
	
	use \PDO;
	use Controllers;
	use Repository;
	use App\Schema;
	use DAO\html_entity;	
	use DAO\html_element as Elemento;
	use DAO\html_type;
	use DAO\html_property;
	use DAO\html_elementproperty AS ElementoPropiedad;
	use Models\html_element;	
	use Models\html_elementproperty;		

	
	class Controller{
	
		protected 	static 	$connection;
		protected 	static 	$conn;
		protected   static  $controller;
		protected   static  $id;
		protected   static  $viewPath;
		protected   static  $controllerPath;
		protected   static  $file;
		protected   static  $fileName;
		protected   static  $startLine;
		protected   static  $endLine;
		public  	static  $view;
		protected   static  $prototype	=	null;
		protected   static  $entityID 	= 	null;
		public      static  $counter    = 	0;
		public      static  $entities 	= array();
		public      static  $strHTML 	=    "";
		public      static  $path;
		private     static  $parentNode;
		private     static  $pastLevel;
	
		
		private static function connect(){
			 
			self::$connection = new PDO(DNS, USER, PASSWORD, array(
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION 
					
				)
			);
			 
			
		}
		
		
		
		
		private static function connectAnyDataBase($database){
			$DNS= "mysql:dbname=$database;host=127.0.0.1;port=3308;charset=utf8"; 
			
			self::$connection = new PDO($DNS, USER, PASSWORD, array(
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION 
					
				)
			);
			 
			
		}




		private static function connectar(){
			$DNS= "mysql:dbname=swapp;host=127.0.0.1;port=3308;charset=utf8";
			self::$conn = new PDO($DNS, USER, PASSWORD, array(
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION 
					 
				)
			);
			 
		
		}
		
		
		
		
		public static function sView($view){
			self::connect();
			$html = new HTML();
			return $html->putHTML($view);
		}
		
		
		
		
		public static function setView($viewName){
			self::getPath();
			$resource = self::$viewPath.$viewName;
			if(file_exists($resource)){
				$content =  file_get_contents($resource);
				return $content;
			} else {
				echo $resource;
			}
		}		
		

		public static function ReadCSS($cssFileName){
			
			
			$viewPath = self::GetSettting('css');

			$selector			= "";
			$token 				= "";
			$previousToken 		= "";
			$startCommentDash 	= false;
			$commentStatus    	= false;
			$allComments		= [];
			$allRules			= [];
			$allSelectors		= [];
			$commentLines 	  	= "";
			$resource 			= $viewPath.$cssFileName;
			$startRules			= false;
			$endRules			= false;
			$ruleCSS			= ""; 
			
			$SelectorRules		= [];
			 
			if(file_exists($resource)){
				 
				$cssFile 	= 	fopen($resource, "r") or die("Unable to open file!");
 
				//Caracter por caracter
				while(!feof($cssFile)) {
					
				 
					//Inicia un comentario
					$token = fgetc($cssFile);
					if($token=="/"){
						$startCommentDash = true;
					}
				  
					//Se consolida el comentario
					if($token=="*" && $startCommentDash == true){
						$commentStatus = true;
					} 
				  
				  
					//Se cierra el comentario y guarda el comentario
					if($commentStatus==true && $token == "/"){
						$commentLines .=$token;
						
						if(strlen($commentLines) >0 ) {
							$allComments[] 	= $commentLines; 
						}
						
						$commentLines 	  	= "";
						$commentStatus 		= false;
						$startCommentDash	= false;
					}  
					

				    //Se guarda el comentario
					if($commentStatus==true && $token != "/"){
						if(strlen($commentLines) >0 ) {
							//$allComments[] 	= $commentLines; 
						}
						
					}
					
					
					//Si son comentarios
					if($commentStatus 	==	 true){
						$commentLines .=$token;  
						
					}  
				  
				  
					if($commentStatus == false){
						
						if($token == "{" ) {
							$startRules = true;
							$endRules   = false;
							//$selector   = "";
						}
					
						if($token == "}") {
							$startRules = false;
							$endRules  	= true;
 						}				

						//Capturar los selectores
						if($startRules == false && $endRules == true){
							if($token != "/" && $token != "}"){
								$selector .= $token;
							}			
							
						}
						
						
						//Logica start=true; end=false;
						if($startRules==true && $endRules == false){
							if(strlen($selector) > 0 ){
								$allSelectors[] = trim($selector);								
							}
							
							$selector ="";
						}
						
						//Logica start=true; end=false;
						//La regla CSS para ese selector
						if($startRules == true && $endRules == false  ){
													
							if(trim($token) !="{"){
								$ruleCSS .= trim($token);
							}
							
						}  				
						
						
						//Logica start=true; end=false;
						if($startRules == false  && $endRules == true   ){
							if(strlen($ruleCSS)> 0){
								$allRules[] = trim($ruleCSS);
							}
							
							$ruleCSS = "";
						}
						
						if($startRules == false && $endRules == false){
							if($token != "/" && $token != "}"){
								$selector .= trim($token);
							}					
							 
						}						
						
					}
					
					 
					 
					
				}				
				 

				 
				
				fclose($cssFile);		

				
				foreach($allComments as $comment){
					//echo $comment."\n";
					//echo "--------------------------------------\n";
				}
				
				
				
				foreach($allRules as $rule){
					//echo $rule."\n";
					//echo "--------------------------------------\n";
				}	

				 
				
				foreach($allSelectors as $el){
					//echo $el."<br>";
					
				}					
				
				//TODO:
				// evitar calc
				// url ../../
				
				if(count($allSelectors) == count($allRules)){
					$SelectorRules = array_combine($allSelectors,$allRules);
					 
				}
				 		
				return $SelectorRules;
			} else {
				echo $resource;
			}
		}

		
		
		
		public static function ShowContent($FileName){
			$path = self::GetSettting("sql");
			$doc_root = preg_replace("!${_SERVER['SCRIPT_NAME']}$!", '', $_SERVER['SCRIPT_FILENAME']); 
			$resource = $doc_root."/".$path.$FileName;
			 
			if(file_exists($resource)){
				$content =  file_get_contents($resource);
				return $content;
			} else {
				echo $resource;
			}
		}	
		
		
		
		
		public static function ShowContentByProject($projectFolder,$FileName){
			$path = self::GetSettting("sql");
			$doc_root = preg_replace("!${_SERVER['SCRIPT_NAME']}$!", '', $_SERVER['SCRIPT_FILENAME']); 
			$resource = $doc_root."/".$projectFolder."/".$path.$FileName;
			 
			if(file_exists($resource)){
				$content =  file_get_contents($resource);
				return $content;
			} else {
				echo $resource;
			}
		}



		
		public static function ShowAnyContent($projectFolder,$FileName,$config){
			
			$doc_root = preg_replace("!${_SERVER['SCRIPT_NAME']}$!", '', $_SERVER['SCRIPT_FILENAME']); 
			
			$path = $doc_root . "/".$projectFolder.'/'.self::GetSettting($config);
			
			$resource = $path.$FileName;
			
			if(file_exists($resource)){
				$content =  htmlentities(file_get_contents($resource));
				return $content;
				exit();
			} else {
				echo $resource;
			}
		}		
		
		
		

		public static function renderView($view,$data=null,$static=1){
		
			$vista = '';
			 
		 
			self::connectar();
		    self::getPageID($view);
			
			$html = new HTML();
			
			$prototype 			 	= 	$html->renderHTML(self::$id);
			 
			
			$strJSON 	=  $prototype;		
			 
			
			if($static==1){
				$vista = $prototype;
			}	else {
				
				$arrayJSON= array();
				 
				if(is_array($data)){
					
					foreach($data as $key => $val ){
						 
						
						$pattern = $strJSON;
						foreach($val as $i => $item){
							 
							 
							 
							$strArray 		= str_replace( "@$i", $item, $pattern );
							$pattern        = $strArray;
						}
						 
						 
					    $strArray		= str_replace("@","",$strArray);
						$vista .= $strArray;
						 

					}
				}
			}
			 
			return $vista;
						
		}
		 
		
		
		
		public static function Page($parm = null){
			$className 			= 		get_called_class();
			$controller 		= 		(	new \ReflectionClass($className))->getShortName();
			$action		 		= 		debug_backtrace()[1]['function'];
			$pageName			= 		$controller . "/" . $action;	

			self::connectar();
		    $result =  self::getPageID($pageName);
			
			if(!$result){
				return false;
			} else {
				$entity = new html_entity();
				$entity->id = self::$id;
				$collection = $entity->getCollectionsByParentID();				
				foreach($collection as $object){
					
					if($object->viewType == "1"){
						$partialView = (array)self::partialView(null,$object->id);
						$array = json_decode($partialView[0],true);
						self::IterateArray($array); 
						 
						return json_encode($array);
					} 
					 
				}				
			}
			
		 
	 

		}



		public static function GetPartialView($viewID){
			
			$entity 	= new html_entity();
			$entity->id = $viewID;
			$object 	= $entity->getObject();		
			$parm 		= 129;
			 
			
			if($object->viewType == "1"){
				$partialView = (array)self::partialView(null,$object->id);
				$array = json_decode($partialView[0],true);
				self::IterateArray($array); 
				  
				return false;
			} 
			 
			if (class_exists($object->classNameSource)) {
				if(method_exists($object->classNameSource, $object->methodNameSource)){
					 
					$class  		= 	new \ReflectionClass($object->classNameSource);	 
					$entity 		= 	new $object->classNameSource();
					 
					$method 		= 	new \ReflectionMethod($object->classNameSource, $object->methodNameSource);
					
					switch($object->executeType){
						case '1':
								$result 		= $method->invoke($entity);
								 
								$partialView 	= self::partialView($result,$object->id);
								break;
								
						case '2':
								if($object->setter == '1'){
									$parameters = $class->getMethod($object->methodNameSource)->getParameters();
									$args 		= Array( $object->argValue );
									$result 	= $method->invokeArgs($entity, $args);									
								} else if($object->setter == '2' ) {
									$parameters = $class->getMethod($object->methodNameSource)->getParameters();
									$args 		= Array( $parm );
									$result 	= $method->invokeArgs($entity, $args);
									
								}
								$partialView = self::partialView($result,$object->id);
								break;		
								
								
						case '3':
								if($object->setter == '1'){
									$reflectionProperty 	= $class->getProperty($object->propertyName);
									$reflectionProperty->setValue($entity, $object->propertyValue);
									$result 				= $method->invoke($entity);									
								} else if($object->setter == '2' ) {
									$reflectionProperty 	= $class->getProperty($object->propertyName);
									
									$reflectionProperty->setValue($entity, intval($parm));
									$result 				= $method->invoke($entity);	
									
									
								}						
								$partialView = self::partialView($result,$object->id); 
								break;
					}
					
					return $partialView;

				} else {
					echo "method does not exist\n";
				}
				
			} else {
					echo "Clase no existe";
			}			
		}



		public static function IterateArray(&$parmArray){
			
			if(is_array($parmArray)){
				 
				foreach($parmArray as &$array){
					 
					if($array['hasChilds']){
						self::IterateArray($array['Childs']);
					} else {
						$subordinatedView = (int)$array['entityView'];
						if($subordinatedView > 0){
							$partialView = self::GetPartialView($subordinatedView);
							$Childs 		 = json_decode($partialView,true);
							$array['hasChilds'] = true;
							$array['Childs'] = $Childs;
						} 
						
					}
				}
			}
		}


		
		public static function View($data=null){
			
			 
			$className 			= 		get_called_class();
			 
			$controller 		= 		(	new \ReflectionClass($className))->getShortName();
			 
			$action		 		= 		debug_backtrace()[1]['function'];
			$viewName			= 		$controller . "/" . $action;
			
		 
			 
			
			$html = new HTML();
			
			self::connectar();
		    $result =  self::getViewID($viewName);
		
			 
			
			$result = array();
			 
			$prototype 	= $html->getElementsJSON(self::$id,0);	 
			
			 
			if($prototype==false){
				return 404;
			}	
			
		
			if(!$data==null){
				
				if(gettype($data)=="object"){
					ob_clean();
					return json_encode(self::SetTree($prototype,$data));
				}if(gettype($data)=="array"){
						 
						foreach($data as $key => $val){
							 
							$temp = self::SetTree($prototype,$val);
							$result[] = $temp[0];
						}
						 
						 
						return json_encode($result);
				}	else {
					
				}
			}   else {
				$prototype 	= $html->getElementsJSON(self::$id,0);
				ob_clean();
				return json_encode($prototype);				
				
			}
			 
			
			
		} 
		
		
		
		
		public static function partialView($data=null,$viewID){
			 
			 
			$html = new HTML();
			 
			
			$result = array();
			 
			$prototype 	= $html->getElementsJSON($viewID,0);	 
			
			 
			if($prototype==false){
				return 404;
			}	
			
		
			if(!$data==null){
				 
				if(gettype($data)=="object"){
					ob_clean();
					return json_encode(self::SetTree($prototype,$data));
				}if(gettype($data)=="array"){
						 
						foreach($data as $key => $val){
							 
							$temp = self::SetTree($prototype,$val);
							$result[] = $temp[0];
						}
						 
						 
						return json_encode($result);
				}	else {
					
				}
			}   else {
				$prototype 	= $html->getElementsJSON($viewID,0);
				ob_clean();
				return json_encode($prototype);				
				
			}
			 
			
			
		}		
		
		
		
		
		public static function View1($view,$data=null,$static=1){
			 
			self::connectar();
		    self::getPageID($view);
			$view = "";
			
			 
			
			$html = new HTML();
			$prototype 	= $html->setHTMLdomJSON(self::$id,0);
			 
			 
			$strJSON 	=  json_encode($prototype);		
			$pattern = $strJSON;
			if($static==1){
				
				if($data==null){
					$view = $prototype;
				} else {
					$arrayJSON= array(); 
					$count 			=	0;
					$keys 			= 	array_keys($data);
					$values 		= 	array_values($data);
					
					$strArray 		= 	str_replace( $keys, $values, $pattern );
					$strArray		= 	str_replace("@","",$strArray);
					$tmpArray 		= 	json_decode($strArray); 
					$arrayJSON[] 	= 	$tmpArray[0];
					$view 			= 	$arrayJSON;
				}
			}	else {
				
				$arrayJSON= array();
				 
				if(is_array($data)){
					
					foreach($data as $key => $val ){
						
						$pattern = $strJSON;
						foreach($val as $i => $item){
							if($i == ''){
								
							}
							$strArray 		= str_replace( "@$i", $item, $pattern );
							$pattern        = $strArray;
						}
						
					    $strArray		= str_replace("@","",$strArray);

						$tmpArray 		= json_decode($strArray);
						
						if(isset($tmpArray[0]->hasChilds)  ){
							if( $tmpArray[0]->hasChilds == true ){
								$tmpArray[0]->dataModel = $val;
							}
						}
						
						$arrayJSON[] 	= $tmpArray[0];
					} 
					 
				}  
				 
				
				
				$view = $arrayJSON;
				
			}
			
	 
			
			 
			return json_encode($view);			 
		 
		 
		}
		
		
		
		
		public static function View2($view,$data=null){
			$html = new HTML();
			$prototype = $html->setHTMLdomJSON(63,0);
			$strJSON =  json_encode($prototype);
			$categories = new htmlcategory();
			$rows 		= $categories->sendRows();
			var_dump($strJSON);
			return false;
			$arrayJSON= array();
			foreach($rows as $key => $val ){
				$strArray = str_replace( array_keys($val), $val, $strJSON);
				$tmpArray = json_decode($strArray);
				$arrayJSON[] = $tmpArray[0];
			} 
			ob_clean();
			return json_encode($arrayJSON);			
		}
		
	  
	  
	  
	    public static function loadNode($array){
			$html = new HTML();
			$prototype = $html->setHTMLdomJSON(self::$id,0);
			 
			 
			if(is_array($array)){
				 
				foreach($array as $key => $val){
					    
				   if($prototype[0]["hasChilds"] == true){
						$element = $prototype[0]["Childs"];
						foreach($element as $id => $value){
							  if($value["fieldName"] == $key){
								  echo $key ." => " . $val."<br>";
							  }
						}
				   }
				}
			}
		}
		
		
		
		
		public static function getFiles($config)
		{
				 
			$path = $_SERVER['DOCUMENT_ROOT'] . "/DOM/".self::GetSettting($config);
			
			if (is_dir($path)) {
				$res =  opendir($path);
				if(!$res){
					return "Error al abrir directorio";
				} else {
					while (($file = readdir($res)) !== false) {
						 if($file=='.' || $file =='..'){ continue; }
						$objJSON[]= array( 
						   
							"id"      		=> $file,
							"fileType"		=> filetype($path.$file)
						); 							
					}
					closedir($res);
					return $objJSON;
				}
			} else {
				return 'Directorio invalido';
			}
		}	




		public static function getGlobalFiles($config,$subCarpeta){
				 
				$path = $_SERVER['DOCUMENT_ROOT'] . "/DOM/".self::GetSettting($config)."/".$subCarpeta."/";
				
				 
				
				if (is_dir($path)) {
					$res =  opendir($path);
					if(!$res){
						return "Error al abrir directorio";
					} else {
						while (($file = readdir($res)) !== false) {
							 if($file=='.' || $file =='..'){ continue; }
							$objJSON[]= array( 
							   
								"id"      		=> $file,
								"fileType"		=> filetype($path.$file)
							); 							
						}
						closedir($res);
						return $objJSON;
					}
				} else {
					return 'Directorio invalido';
				}
		}	
		
		
		
		
		public static function getGlobalFilesByProject($projectFolder,$config,$subCarpeta){
			 
			if(empty($subCarpeta)){
				 
				$path = $_SERVER['DOCUMENT_ROOT'] . "/$projectFolder/".self::GetSettting($config);
			} else {
				$path = $_SERVER['DOCUMENT_ROOT'] . "/$projectFolder/".self::GetSettting($config)."/".$subCarpeta."/";
			}
			
			 
			 
			
			if (is_dir($path)) {
				$res =  opendir($path);
				if(!$res){
					return "Error al abrir directorio";
				} else {
					while (($file = readdir($res)) !== false) {
						 if($file=='.' || $file =='..'){ continue; }
						$objJSON[]= array( 
						   
							"id"      		=> substr($file, 0, -4),
							"fileType"		=> filetype($path.$file)
						); 							
					}
					closedir($res);
					return $objJSON;
				}
			} else {
				return 'Directorio invalido';
			}
		}		
	
	
	
	
		private static function getPageID($page){
		 
		$sql = "call getPageID(:i_page);";
		$res = self::$conn->prepare( $sql);	
		$res->bindValue(":i_page", 	$page,  PDO::PARAM_STR);			
		$result = $res->execute();
		
		$row = $res->fetch(PDO::FETCH_OBJ);		
			
		 
		if($row==false){
			return false;
		} else {
			self::$id = $row->id;	
			return true;
		}
					
	
	}
	


		private static function getViewID($view){
		
		$sql = "call getViewID(:view);";
		$res = self::$conn->prepare( $sql);	
		$res->bindValue(":view", 	$view,  PDO::PARAM_STR);			
		$result = $res->execute();
		
		$row = $res->fetch(PDO::FETCH_OBJ);		
		if($row==false){
			return false;
		} else {
			self::$id = $row->id;	
		}
					
	
	}


		
		private static function GetSettting($filter){
			self::connect();
			$sql = "call getSettings(:i_filter);";
			$res = self::$connection->prepare( $sql);	
			$res->bindValue(":i_filter", $filter,  PDO::PARAM_STR);
            $res->execute();
			$row = $res->fetch(PDO::FETCH_OBJ);		
		    return $row->value;
		}		
		
		
		
		
		private static function getPath(){
			self::connect();
			$sql = "call getSettings(:i_filter);";
			$res = self::$connection->prepare( $sql);	
			$res->bindValue(":i_filter", 'path',  PDO::PARAM_STR);
            $res->execute();
			$row = $res->fetch(PDO::FETCH_OBJ);		
            self::$viewPath = $row->value;			
		
		}
		
		
		
		
		private static function getControllerPath(){
			self::connect();
			$sql = "call getSettings(:i_filter);";
			$res = self::$connection->prepare( $sql);	
			$res->bindValue(":i_filter", 'controller',  PDO::PARAM_STR);
            $res->execute();
			$row = $res->fetch(PDO::FETCH_OBJ);		
            self::$controllerPath = $row->value;			
		   
		}		
		
		
		
		
		public static function setFK($fkName,$parent,$node,$localID,$foreingID){
			self::connect();
			$sql = "";
			$sql = ' ALTER TABLE '.$node;
			$sql .= ' ADD CONSTRAINT '.$fkName;
			$sql .= ' FOREIGN KEY ('.$localID.') REFERENCES '.$parent.'('.$foreingID.');';
			$res = self::$connection->prepare( $sql);	
			 
          
			 
		
		}
		
		
		
		
		public static function compileSP($database,$sp){
			
			try{
				 
				self::connectAnyDataBase($database);
				$cmd = self::$connection->prepare( $sp);	
				$res  = $cmd->execute();
				
				 
				if(!$res){
					 
				} else {
					return "<h1>Duh!</h1>";
				}
				
				
			} catch (\Exception $e){
				 
				 	 
			}  
			 
		
		}		
		
		
	
	
		public static function createView($Path,$View,$content){
		 
			$myfile = fopen(self::$viewPath.$Path."/".$View.".html", "w") or die("Unable to open file!");
			$result = fwrite($myfile, $content);
			fclose($myfile);
			return $result;
		}
		
		
		
		
		//public static function html($Folder,$View,$content){
			// if(!mkdir(self::$viewPath.$Folder, 0777, true)) {
				// die('Fallo al crear las carpetas...');
			// }
		 
			// $myfile = fopen(self::$viewPath.$Folder."/".$View.".html", "w") or die("Unable to open file!");
			// $result = fwrite($myfile, $content);
			// fclose($myfile);
			// return $result;
			 
		// }
		
		
		
		
		public static function getInfo($id){
		 
		// create curl resource 
			$ch = curl_init(); 

			// set url 
			curl_setopt($ch, CURLOPT_URL, "http://localhost/codeGenerator/themeComponent/render/".$id); 

			//return the transfer as a string 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

			// $output contains the output string 
			$output = curl_exec($ch); 

			// close curl resource to free up system resources 
			curl_close($ch);      
			return $output;
			
		}
		
		
		
		
		public static function getLine(){
			
			self::getControllerPath();
			 
			$path = self::$controllerPath."testController.php";
			 
			$handle = fopen($path, "r");
			$result = "";
			if ($handle){
				while (($buffer = fgets($handle, 4096)) !== false) {
					$result .= $buffer."<br>";
				}
				if (!feof($handle)) {
					echo "Error: unexpected fgets() fail\n";
				}
				fclose($handle);
			}
			
			return $result; 
		}
		
		
		
		
		public static function getActions($controllerName){
			
/*			
		    $obj = new Controllers\AngularController();
			return $obj->hola();
*/			
			
			$class = new \ReflectionClass( "Repository". DS . $controllerName );
			$methods = $class->getMethods();
			 
			foreach($methods as $key => $value ){
				$actions[] = array(
					"id"   	=> $value->name
					//"class" => $value->class
 				);
			}
			return $actions;			
			
			 
			
			
		}
		
		
		
		public static	function ClassMethods($namespace,$className){
			
			$class = new \ReflectionClass($namespace .DS. $className );
			
			$methods = $class->getMethods();
			 
			foreach($methods as $key => $value ){
				$actions[] = array(
					"id"   	=> $value->name,
					"class" => $value->class
 				);
			}
			return $actions;			
			
		}		
		
		

		public static	function ClassProperties($namespace,$className){
			
			$class = new \ReflectionClass($projectName .DS. $namespace .DS. $className );
			$classNameTemp = $namespace .DS. $className;
			
			$properties = $class->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED);;
			 
			foreach($properties as $key => $value ){
				if($value->class == $classNameTemp){
					$props[] = array(
						"name"   	=> $value->name,
						"class"     => $value->class
					);
				}
			}
			return $props;			
			
		}


		
		public static	function ClassMethodList($projectName,$namespace,$className){
			
			 
			
			$class =  "..".DS.$projectName .DS. "Modules".DS.$namespace .DS. $className.".php";
			
			$props = [];
		 
			if(is_readable($class)){ 
			
				require_once $class;
				
				$properties = [];
				
				$properties = get_class_methods($namespace .DS. $className);
				 
				$entity = $namespace .DS. $className;
				
				foreach($properties as $key => $value ){
					 
					 
					$props[] = array(
						"name"   	=> $value,
						"class"     => $entity
					);	 
					
					 
				}
				 
			
						
			} else { 
				 
				 
			}
			 
			return $props;		
			 
					
			
		}		
		
		
		
		public static	function ClassPropertyList($projectName,$namespace,$className){
			
			 
			
			$class =  "..".DS.$projectName .DS. "Modules".DS.$namespace .DS. $className.".php";
			
			$props = [];
		 
			if(is_readable($class)){ 
			
				require_once $class;
				
				$properties = [];
				
				$properties = get_class_vars($namespace .DS. $className);
				 
				$entity = $namespace .DS. $className;
				
				foreach($properties as $key => $value ){
					 
					 
					$props[] = array(
						"name"   	=> $key,
						"class"     => $entity
					);	 
					
					 
				}
				 
			
						
			} else { 
				 
				 
			}
			 
			return $props;		
			 
					
			
		}		
		
		
		
		public static function getMembers(){
			$class_members = get_class_vars('testController');
			return $class_members;
		}		
		
		
		
		
		public static function getContent($controller,$action){
			 
			$path  = "C:/wamp2/www/codeGenerator/App/Repository/".$controller.".php";
			
			
			$class = new \ReflectionClass("Repository\\".$controller);
			$export = $class->getMethod($action);
			$method = new \ReflectionMethod($export->class, $export->name);
			self::$startLine = $method->getStartLine();
			self::$endLine   = $method->getEndLine() ;
			$content = "";
			$i=0;
			
			self::$file = file($path);
		 
			
			$i 		= self::$startLine;
			$tope 	= self::$endLine;
			
			for($i;$i<=$tope;$i++){
				$content .= self::$file[$i];
			}
			 
			return $content;
		 
			
		}
		
		
		
		
		public static function recursiveView($view,$data=null,$static=1){
			
			$html = new HTML();
		 
			if(is_array($data)){
			 
				foreach($data as $key => $val){
					
					$prototype 		= 	$html->setHTMLdomJSON($val['entityID'],0); 
					$strJSON 		=  	json_encode($prototype);		
					$pattern 		= 	$strJSON;
					$strArray 		= 	str_replace( 'menu_option' , $val['menu_option'], $pattern );
					$strArray		= 	str_replace("@","",$strArray);
					$tmpArray 		= 	json_decode($strArray);	
					 
					if($val['hasChilds']){
						
						if(isSet($tmpArray[0]->Childs[1]->hasChilds)){
							$tmpArray[0]->Childs[1]->hasChilds = true;
							$tmpArray[0]->Childs[1]->Childs[0] = self::recursiveView($view,$val['Childs'],0);
						}
					}
					
					
					if(isSet($tmpArray[0])){
						self::$entities[] = $tmpArray;
					}
					
					
				}
		 
				
				return self::$entities;
			}
			
			
		}
		
		
		
		
		public static function recursiveHTML($data=null,$parent){
			
			$html = new HTML();
			 
			//$strParent = ""; 
			
			if(is_array($data)){
				
				//Inicializar todos los nodos
				$nodes						=      	"";
				
				foreach($data as $key => $val){
					//Limpiar nodo
					$node      				= 		"";
					//obtener prototipo HTML
					//TODO: Si ya esta en memoria el prototipo no llamar a la base de datos :(
					$prototype 			 	= 		$html->renderHTML($val['entityID']); 
					//Es un simple 'string' 
					$strJSON 			 	=  		$prototype;		
					//El patron HTML
					$pattern 			 	= 		$strJSON;
					//Reemplazar los menus
					$strArray 			 	= 		str_replace( 'menu_option' , $val['menu_option'], $pattern );
					//Reemplazar iconos
				    $strArray 			 	= 		str_replace( 'ti-home' , $val['menu_ico'], $strArray );
					//Reemplazar los enlaces
				    $strArray 			 	= 		str_replace( 'url' , $val['menu_url'], $strArray );
					//Quitar los '@'
					$strArray			 	= 		str_replace("@","",$strArray);
					//Asignar al nodo actual 
					$node   				=     	$strArray;
					
					//Si este nodo tiene ramas hijas llamar recursivamente
					if($val['hasChilds']){
						//Reiniciar cada nodo
						$node			 =  	self::recursiveHTML($val['Childs'],$node); 
					}
			
					//Agregar a la lista padre 
					$nodes	.= $node; 				
					
				}
				
				//Agregar nodos al nodo padre
				$parent		  = 	   str_replace("content",$nodes,$parent);
				
			}	
			
			//Retonar para la siguiente llamada recursiva o retornar a la funcion que llamó
			return $parent;
			
		}
		
		
		
		
		private function setHTMLelement($search,$replace,$pattern){
			return str_replace( $search , $replace, $pattern );
		}
		
		
		
		
		public  static function createNewDirectory($ruta){
			
			if( mkdir($ruta, 0700)){
				self::copyFiles($ruta);
				self::createMainFolders($ruta);
				self::copyAppFiles($ruta);
				self::createModulesFolders($ruta);
				self::copyIntefaces($ruta);
				self::copyModels($ruta);
				self::copyJSLibs($ruta);
				return true;
			}
		}
		
		
		
		
		public static function copyFiles($project){
			
			$rootSource    			=  "c:/wamp2/www/DOM/";
			$rootTarget    			=  $project."/";
			 
			
			$files = array(
				"index" 		=> "index.php",
				"autoload"		=> "Autoload.php",
				"settings"		=> "Settings.php",
				"htacces"		=> ".htaccess"
			);
			
			foreach($files as $key => $val){
				$template 	= $rootSource.$val;
				$newFile 	= $rootTarget.$val;
				
				if (!copy($template,$newFile )) {
					echo "Error al copiar ...\n";
				}				
			}


			
		}
				
		
		
		
		public static function createMainFolders($project){
			$rootTarget    			=  $project."/";
			$folders = array(
				"app" 			=> "App",
				"assets"		=> "Assets",
				"upload"		=> "Upload",
				"img"			=> "Img",
				"modules"		=> "Modules"
			);	
			
			foreach($folders as $key => $value){
				$newFolder 	= $rootTarget.$value;
				if( mkdir($newFolder, 0700)){
				}					
			} 

			 		
		}
		
		
		

		public static function copyAppFiles($project){
			
			$rootSource    			=  "c:/wamp2/www/DOM/App/";
			$rootTarget    			=  $project."/App/";
			 
			
			$files = array(
				"controller" 	=> "Controller.php",
				"bootstrap"		=> "Bootstrap.php",
				"registry"		=> "Registry.php",
				"request"		=> "Request.php"
			);
			
			foreach($files as $key => $val){
				$template 	= $rootSource.$val;
				$newFile 	= $rootTarget.$val;
				
				if (!copy($template,$newFile )) {
					echo "Error al copiar ...\n";
				}				
			}
		}		
		
		
		
		
		
		public static function createModulesFolders($project){
			
			 
			$rootTarget    			=  $project."/Modules/";
			 
			
			$folders = array(
				"controller" 	=> "Controller",
				"dao"			=> "DAO",
				"models"		=> "Models",
				"interfaces"	=> "Interfaces",
				"views"			=> "Views",
				"script"		=> "Script"
				 
			);
			
			foreach($folders as $key => $value){
				$newFolder 	= $rootTarget.$value;
				if( mkdir($newFolder, 0700)){
					
				}					
			} 
			
			$newFolder 	= $rootTarget."/Script/Lib";
			if(mkdir($newFolder,0700)){}
			
			$newFolder 	= $rootTarget."/Script/Events";
			if(mkdir($newFolder,0700)){}
			
		}		
		
		
		
		
		public static function copyModels($project){
			
			$rootSource    			=  "c:/wamp2/www/DOM/Modulos/Models/";
			$rootTarget    			=  $project."/Modules/Models/";
			 
			
			$files = array(
				"response" 			=> "Response.php",
				"daoException"		=> "DAOException.php",
				 
			);
			
			foreach($files as $key => $val){
				$template 	= $rootSource.$val;
				$newFile 	= $rootTarget.$val;
				
				if (!copy($template,$newFile )) {
					echo "Error al copiar ...\n";
				}				
			}
		}	

		
		
		
		public static function copyIntefaces($project){
			
			$rootSource    			=  "c:/wamp2/www/DOM/Modulos/Interfaces/";
			$rootTarget    			=  $project."/Modules/Interfaces/";
			 
			
			$files = array(
				"idao" 				=> "IDAO.php",
				"idocument"			=> "IDocument.php",
				"imodel"			=> "IModel.php",
				"inodedao"			=> "INodeDAO.php",
				"inodemodel"		=> "INodeModel.php",
				"iparentdao"		=> "IParentDAO.php",
				"iparentmodel"		=> "IParentModel.php"
				 
			);
			
			foreach($files as $key => $val){
				$template 	= $rootSource.$val;
				$newFile 	= $rootTarget.$val;
				
				if (!copy($template,$newFile )) {
					echo "Error al copiar ...\n";
				}				
			}
		}	

		
		
		
		public static function copyJSLibs($project){
			
			$rootSource    			=  "c:/wamp2/www/DOM/Modulos/Script/Lib/";
			$rootTarget    			=  $project."/Modules/Script/Lib/";
			 
			
			$files = array(
				"binding" 			=> "Binding.js",
				"dom"				=> "DOM.js",
				"element"			=> "Element.js",
				"request"			=> "Request.js",
			 
				 
			);
			
			foreach($files as $key => $val){
				$template 	= $rootSource.$val;
				$newFile 	= $rootTarget.$val;
				
				if (!copy($template,$newFile )) {
					echo "Error al copiar ...\n";
				}				
			}
		}	




		public function getElements($view){
			$html = new HTML();
			self::connectar();
		    self::getPageID($view);
			 
			$prototype 			 	= 		$html->renderHTML(self::$id); 
			return $prototype;
		}
		
		
		
		
		public function getElementsByEntityID($EntityID){
			$html = new HTML();
			self::connectar();
		    //self::getPageID($view);
			 
			$prototype 			 	= 		$html->renderHTML($EntityID); 
			 
			return $prototype;
		}		
		
		
		
		
		public static function GetEntities($dbName){
			$db 					= 		new Database();
			$entities 			 	= 		$db->getEntities($dbName); 
			return $entities;
		}		
		
		
		
		
		public static function GetProperties($dbName,$entityName){
			$db 					= 		new Database();
			$properties 			= 		$db->getProperties($dbName,$entityName); 
			return $properties;
		}
		
		
		
		
		public static function SetBackendModel($projectFolder,$model,$properties){
			
			$doc_root = preg_replace("!${_SERVER['SCRIPT_NAME']}$!", '', $_SERVER['SCRIPT_FILENAME']); 
			
			if(is_array($properties)){
				
				$content = "";
				 
				foreach($properties as $property){
					$content.="public $".$property["propertyName"].";\n\t";
				}
						
			     
				$file 		= __dir__ ."/Templates/modelBackEnd.php";
				$fileContent = file_get_contents($file);
				 
				
				$content1 = str_replace("modelName",$model,$fileContent);
				 
				$content2 = str_replace("properties",$content,$content1);
				 
				
				//$path = self::GetSettting("models");
				
				$path = $doc_root ."/".$projectFolder ."/".self::GetSettting("models");	
				
				$fullPath = $path.$model;
				
				 		
						 
				$myfile = fopen($path.$model.".php", "w") or die("Unable to open file!");
				$result = fwrite($myfile, $content2);
				fclose($myfile);
				return $result;
				
				
			}
		}
	

	
		
		public static function SetFrontEndModel($projectFolder,$model,$properties){
			
			
			if(is_array($properties)){
				 
				$args = "";
				
				$total = count($properties);
				$c=0;
				foreach($properties as $property){
					$c++;
					if($c==$total){
						$args.= $property["propertyName"]."\n\t\t\t";
					} else if($c==1){
						$args.= "\n\t\t\t".$property["propertyName"]."\n\t\t\t";
					} else {
						$args.= $property["propertyName"].",\n\t\t\t";
					}
					 
				}				
				
				
				
				$content = "";
				$c = 0;
				foreach($properties as $property){
					 
					$c++;
					if($c>1){
						$content.="this.".$property["propertyName"]." = " .$property["propertyName"]."; \n\t\t\t";
					} else {
						$content.="this.".$property["propertyName"]." = " .$property["propertyName"]."; \n\t\t\t";
					}
				}
						
						
				$null = "";
				 
				$c = 0; 
				foreach($properties as $property){
					
					$c++;
					if($c>1){
						$null.="this.".$property["propertyName"]." = null; \n\t\t\t";
					} else {
						$null.="this.".$property["propertyName"]." = null; \n\t\t\t";
					}
				}						
			     
				$file 		= __dir__ ."/Templates/modelFrontEnd.tpl";
				$fileContent = file_get_contents($file);
				 
				
				$content1 = str_replace("modelName",$model,$fileContent);
				 
				$content2 = str_replace("args",$args,$content1);
				
				$content3 = str_replace("contructors",$content,$content2);
				
				$content4 = str_replace("setNull",$null,$content3);
				 
				
				$path = self::GetSettting("js/models");
				
				$doc_root = preg_replace("!${_SERVER['SCRIPT_NAME']}$!", '', $_SERVER['SCRIPT_FILENAME']); 
				
				$path = $doc_root ."/".$projectFolder ."/".self::GetSettting("js/models");	
				//$fullPath = $path.$model;
				 
			 	 
				$myfile = fopen($path.$model.".js", "w") or die("Unable to open file!");
				$result = fwrite($myfile, $content4);
				fclose($myfile);
				return $result;
				
				
			}
		}		
		
	

	
		public function SetDAO($projectFolder,$entity,$model){
			
			$doc_root = preg_replace("!${_SERVER['SCRIPT_NAME']}$!", '', $_SERVER['SCRIPT_FILENAME']); 
			 
			
			$file 		= __dir__ ."/Templates/DAO.TPL";
			$fileContent = file_get_contents($file);	

			$content1 = str_replace("@entity",$entity,$fileContent);	

			$content2 = str_replace("@model",$model,$content1);
			
			$path = $doc_root ."/".$projectFolder ."/".self::GetSettting("dao");
			 
			 
					 
			$myfile = fopen($path.$model.".php", "w") or die("Unable to open file!");
			$result = fwrite($myfile, $content2);
			fclose($myfile);
			return $result;
			
		}
	

	
		
		public function SetPROCEDURE_ADD($folderProject,$dbName,$entityName){
			 
			 
			
			$db = new Database();
			 
			$result = "";
			
			$file 		= __dir__ ."/Templates/add_row_insert.TPL";
			
			$fileContent = file_get_contents($file);	

			$fields = $db->getProperties($dbName,$entityName);
			
			$fieldNames = array();
			
			foreach($fields as $field){
				if( $field['type'] != 'DATETIME' || $field['type'] != 'DATE'){
					$parameters[] = "i_".$field['propertyName']."	".$field['type2'];
				} else {
					$parameters[] = "i_".$field['propertyName']."	".$field['type'];
				}
			}
			 
			foreach($fields as $field){
				$fieldNames[] = $field['propertyName'];
			}
			
			foreach($fields as $field){
				$valores[] = "i_".$field['propertyName'];
			}

			$params =   implode(",\r\n",$parameters);

			$campos = 	implode(",\r\n",$fieldNames);
			
			$values =   implode(",\r\n",$valores);

			
			$content = str_replace("@command","SP_ADD_" . $entityName,$fileContent);	
			
			$content0 = str_replace("@entity",$entityName,$content);	
			
			$content1 = str_replace("@params",$params,$content0);	

			$content2 = str_replace("@fields",$campos,$content1);
			
			$content3 = str_replace("@values",$values,$content2);
			
			$path = self::GetSettting("sql");
			 
			 
			$doc_root = preg_replace("!${_SERVER['SCRIPT_NAME']}$!", '', $_SERVER['SCRIPT_FILENAME']); 
			$ruta = $doc_root ."/".$folderProject."/".$path.$entityName;
			 
			 
			 
			
			if(!is_dir($ruta)){
				 
				if(!mkdir($ruta, 0777, true)) {
					 
				}	else {
					$myfile = fopen($ruta."/SP_ADD_".$entityName.".sql", "w") or die("Unable to open file!");
					$result = fwrite($myfile, $content3);
					fclose($myfile);
					return $result;						
					
				}						
			} else {
				$myfile = fopen($ruta."/SP_ADD_".$entityName.".sql", "w") or die("Unable to open file!");
				$result = fwrite($myfile, $content3);
				fclose($myfile);
				return $result;	
			}
			 


			
	
			
		}
	

		
		
		public function SetPROCEDURE_EDIT($folderProject,$dbName,$entityName){
			
			$db = new Database();
			 
			 
			$result 		= 	"";
			
			
			$file 			= 	__dir__ ."/Templates/edit_row_update.TPL";
			
			
			$fileContent 	= file_get_contents($file);	

			$fields 		= $db->getProperties($dbName,$entityName);
			
			$fieldNames 	= array();
			
			foreach($fields as $field){
				if( $field['type'] != 'DATETIME' || $field['type'] != 'DATE'){
					$parameters[] = "i_".$field['propertyName']."	".$field['type2'];
				} else {
					$parameters[] = "i_".$field['propertyName']."	".$field['type'];
				}
			}
			
			 
			foreach($fields as $field){
				$assignments[] = $field['propertyName'] . "=" ."i_".$field['propertyName'] ;
			}
			
			
		 

			$params 	=   implode(",",$parameters);
	
			$campos = implode(",",$assignments);
			 

			
			$content 	= 	str_replace("@command","SP_EDIT_" . $entityName,$fileContent);	
				
			$content0 	= 	str_replace("@entity",$entityName,$content);	
				
			$content1 	= 	str_replace("@params",$params,$content0);	
	
			$content2 	= 	str_replace("@assignments",$campos,$content1);
			
			 
			
			$path = self::GetSettting("sql");
			 
			$doc_root = preg_replace("!${_SERVER['SCRIPT_NAME']}$!", '', $_SERVER['SCRIPT_FILENAME']); 
			 
			$ruta = $doc_root ."/".$folderProject."/".$path.$entityName;
			 
			 
			if(!is_dir($ruta)){
				if(!mkdir($ruta, 0777, true)) {
					 
				}	else {
					$myfile = fopen($ruta."/SP_EDIT_".$entityName.".sql", "w") or die("Unable to open file!");
					$result = fwrite($myfile, $content2);
					fclose($myfile);
					return $result;						
					
				}						
			} else {
				$myfile = fopen($ruta."/SP_EDIT_".$entityName.".sql", "w") or die("Unable to open file!");
				$result = fwrite($myfile, $content2);
				fclose($myfile);
				return $result;					
			}		
			
		}
	


		
		public function SetPROCEDURE_DELETE($folderProject,$dbName,$entityName){
			
			$db = new Database();
			 
			$result 		= 	"";
			
			$file 			= 	__dir__ ."/Templates/remove_row_delete.TPL";
			
			$fileContent 	= file_get_contents($file);	
			
			$content 	= 	str_replace("@command","SP_DELETE_" . $entityName,$fileContent);	
				
			$content0 	= 	str_replace("@entity",$entityName,$content);	
			
			$path = self::GetSettting("sql");
			
			
			$doc_root = preg_replace("!${_SERVER['SCRIPT_NAME']}$!", '', $_SERVER['SCRIPT_FILENAME']); 
			$ruta = $doc_root ."/".$folderProject."/".$path.$entityName;
			
			 
			 
			 
			if(!is_dir($ruta)){
				if(!mkdir($ruta, 0777, true)) {
					 
				}	else {
					$myfile = fopen($ruta."/SP_DELETE_".$entityName.".sql", "w") or die("Unable to open file!");
					$result = fwrite($myfile, $content0);
					fclose($myfile);
					return $result;						
					
				}						
			} else {
					$myfile = fopen($ruta."/SP_DELETE_".$entityName.".sql", "w") or die("Unable to open file!");
					$result = fwrite($myfile, $content0);
					fclose($myfile);
					return $result;					
				
			}	
			
		}	




		public function SetPROCEDURE_GETALL($folderProject,$dbName,$entityName){
			
			$db = new Database();
			 
			$result = "";
			
			$file 		= __dir__ ."/Templates/getall_rows_select_all.TPL";
			
			$fileContent = file_get_contents($file);	

			$fields = $db->getProperties($dbName,$entityName);
			
			$fieldNames = array();
			
			foreach($fields as $field){
				if( $field['type'] != 'DATETIME' || $field['type'] != 'DATE'){
					$parameters[] = "i_".$field['propertyName']."	".$field['type2'];
				} else {
					$parameters[] = "i_".$field['propertyName']."	".$field['type'];
				}
				
				
			}
			 
			foreach($fields as $field){
				$fieldNames[] = $field['propertyName'];
			}
			
			foreach($fields as $field){
				$valores[] = "i_".$field['propertyName'];
			}

			 

			$campos = 	implode(",",$fieldNames);
			
			 

			
			$content = str_replace("@command","SP_SELECT_ALL_" . $entityName,$fileContent);	
			
			$content0 = str_replace("@entity",$entityName,$content);	
			
			$content1 = str_replace("@fields",$campos,$content0);
	
			
			$path = self::GetSettting("sql");
			 
			$doc_root = preg_replace("!${_SERVER['SCRIPT_NAME']}$!", '', $_SERVER['SCRIPT_FILENAME']); 
			$ruta = $doc_root ."/".$folderProject."/".$path.$entityName;
			 
			 
			if(!is_dir($ruta)){
				if(!mkdir($ruta, 0777, true)) {
					 
				}	else {
					$myfile = fopen($ruta."/SP_SELECT_ALL_".$entityName.".sql", "w") or die("Unable to open file!");
					$result = fwrite($myfile, $content1);
					fclose($myfile);
					return $result;						
					
				}						
			} else {
					$myfile = fopen($ruta."/SP_SELECT_ALL_".$entityName.".sql", "w") or die("Unable to open file!");
					$result = fwrite($myfile, $content1);
					fclose($myfile);
					return $result;					
				
			}	
			
		}
	

	
		
		public function SetPROCEDURE_GETBYID($folderProject,$dbName,$entityName){
			
			$db = new Database();
			 
			$result = "";
			
			$file 		= __dir__ ."/Templates/getbyid_row_select_row.TPL";
			
			$fileContent = file_get_contents($file);	

			$fields = $db->getProperties($dbName,$entityName);
			
			$fieldNames = array();
			
			foreach($fields as $field){
				if( $field['type'] != 'DATETIME' || $field['type'] != 'DATE'){
					$parameters[] = "i_".$field['propertyName']."	".$field['type2'];
				} else {
					$parameters[] = "i_".$field['propertyName']."	".$field['type'];
				}
				
				
			}
			 
			foreach($fields as $field){
				$fieldNames[] = $field['propertyName'];
			}
			
			foreach($fields as $field){
				$valores[] = "i_".$field['propertyName'];
			}

			 

			$campos = 	implode(",",$fieldNames);
			
			 

			
			$content = str_replace("@command","SP_SELECT_ROW_" . $entityName,$fileContent);	
			
			$content0 = str_replace("@entity",$entityName,$content);	
			
			$content1 = str_replace("@fields",$campos,$content0);
	
			
			$path = self::GetSettting("sql");
			 
			$doc_root = preg_replace("!${_SERVER['SCRIPT_NAME']}$!", '', $_SERVER['SCRIPT_FILENAME']); 
			$ruta = $doc_root ."/".$folderProject."/".$path.$entityName;
			 
			 
			if(!is_dir($ruta)){
				if(!mkdir($ruta, 0777, true)) {
					 
				}	else {
					$myfile = fopen($ruta."/SP_SELECT_ROW_".$entityName.".sql", "w") or die("Unable to open file!");
					$result = fwrite($myfile, $content1);
					fclose($myfile);
					return $result;						
					
				}						
			} else {
					$myfile = fopen($ruta."/SP_SELECT_ROW_".$entityName.".sql", "w") or die("Unable to open file!");
					$result = fwrite($myfile, $content1);
					fclose($myfile);
					return $result;					
			}		
			
		}
	

	
		
		public function SetPROCEDURE_GETBYFILTER($folderProject,$dbName,$entityName){
			
			$db = new Database();
			 
			$result = "";
			
			$file 		= __dir__ ."/Templates/getall_rows_select_byfilter.TPL";
			
			$fileContent = file_get_contents($file);	

			$fields = $db->getProperties($dbName,$entityName);
			$countArray = count($fields);
			$fieldNames = array();
			$filters 	= [];
			
			$iArray = 0;
			foreach($fields as $field){
			    
				if( $field['type'] == 'varchar' ){
		
					if($iArray==$countArray){
						$filters[] = $field['propertyName']." LIKE CONCAT('%', i_filter , '%')";
						
					} else {
						
						$filters[] = $field['propertyName']." LIKE CONCAT('%', i_filter , '%')";
					}					
					
					
				} 
				
				$iArray++;
			}			
			
			$setFilter = "";
			
			$iArray=0;
			$countArray = count($filters) - 1;
			foreach($filters as $filter){
				if($iArray==$countArray){
					$setFilter.= $filter . "";
				} else {
					
					$setFilter.= $filter.  " OR  \n";
				}
				
				$iArray++;
			}
			 
			 
			 
			
			foreach($fields as $field){
				if( $field['type'] != 'DATETIME' || $field['type'] != 'DATE'){
					$parameters[] = "i_".$field['propertyName']."	".$field['type2'];
				} else {
					$parameters[] = "i_".$field['propertyName']."	".$field['type'];
				}
			} 
			 
			 
			foreach($fields as $field){
				$fieldNames[] = $field['propertyName'];
			}
			
			foreach($fields as $field){
				$valores[] = "i_".$field['propertyName'];
			}
			 

			$campos = 	implode(",",$fieldNames);

			
			$content = str_replace("@command","SP_SELECT_ROWS_FILTER_" . $entityName,$fileContent);	
			
			$content0 = str_replace("@entity",$entityName,$content);	
			
			$content1 = str_replace("@fields",$campos,$content0);
			
			$content2 = str_replace("@filters",$setFilter,$content1);
	
			
			$path = self::GetSettting("sql");
			 
			$doc_root = preg_replace("!${_SERVER['SCRIPT_NAME']}$!", '', $_SERVER['SCRIPT_FILENAME']); 
			$ruta = $doc_root ."/".$folderProject."/".$path.$entityName;
			 
			 
			if(!is_dir($ruta)){
				if(!mkdir($ruta, 0777, true)) {
					 
				}	else {
					$myfile = fopen($ruta."/SP_SELECT_ROWS_FILTER_".$entityName.".sql", "w") or die("Unable to open file!");
					$result = fwrite($myfile, $content2);
					fclose($myfile);
					return $result;						
					
				}						
			} else {
					$myfile = fopen($ruta."/SP_SELECT_ROWS_FILTER_".$entityName.".sql", "w") or die("Unable to open file!");
					$result = fwrite($myfile, $content2);
					fclose($myfile);
					return $result;					
			}		
			
		}		
		
		
		
		
		public function SetPROCEDURE_GETALL_BYFILTER($folderProject,$dbName,$entityName){
			
			$db = new Database();
			 
			$result = "";
			
			$file 		= __dir__ ."/Templates/getall_select_byfilter.TPL";
			
			$fileContent = file_get_contents($file);	

			$fields = $db->getProperties($dbName,$entityName);
			
		
			foreach($fields as $field){
				if( $field['type'] != 'DATETIME' || $field['type'] != 'DATE'){
					$parameters[] = "i_".$field['propertyName']."	".$field['type2'];
				} else {
					$parameters[] = "i_".$field['propertyName']."	".$field['type'];
				}
			}			
			
			$countArray = count($fields) - 1;
			$fieldNames = array();
			$filters 	= [];
			
			$iArray = 0;
			foreach($fields as $field){
			    
				if( $field['type'] == 'varchar' ){
		
					if($iArray==$countArray){
						$filters[] = $field['propertyName']." LIKE CONCAT('%', i_".$field['propertyName'].", '%')";
						
					} else {
						
						$filters[] = $field['propertyName']." LIKE CONCAT('%', i_".$field['propertyName'].", '%')";
					}					
					
					
				} else {
	
					if($iArray==$countArray){
						$filters[] = $field['propertyName']." =  i_".$field['propertyName']."";
						
					} else {
						
						$filters[] = $field['propertyName']." =  i_".$field['propertyName']."";
					}							
					
				}
				
				$iArray++;
			}			
			
			$setFilter = "";
			
			$iArray=0;
			$countArray = count($filters) - 1;
			foreach($filters as $filter){
				if($iArray==$countArray){
					$setFilter.= $filter . "";
				} else {
					
					$setFilter.= $filter.  " OR  \n";
				}
				
				$iArray++;
			}
			 
			 
			 
			
			// foreach($fields as $field){
				// if( $field['type'] != 'DATETIME' || $field['type'] != 'DATE'){
					// $parameters[] = "i_".$field['propertyName']."	".$field['type2'];
				// } else {
					// $parameters[] = "i_".$field['propertyName']."	".$field['type'];
				// }
			// } 
			 
			 
			foreach($fields as $field){
				$fieldNames[] = $field['propertyName'];
			}
			
			foreach($fields as $field){
				$valores[] = "i_".$field['propertyName'];
			}
			 

			$campos = 	implode(",",$fieldNames);
			
			$params =   implode(",",$parameters);
			
			$content = str_replace("@command","SP_SELECT_ALL_ROWS_FILTER_" . $entityName,$fileContent);	
			
			$content0 = str_replace("@entity",$entityName,$content);	
			
			$content1 = str_replace("@params",$params,$content0);	
			
			$content2 = str_replace("@fields",$campos,$content1);
			
			$content3 = str_replace("@filters",$setFilter,$content2);
	
			
			$path = self::GetSettting("sql");
			 
			$doc_root = preg_replace("!${_SERVER['SCRIPT_NAME']}$!", '', $_SERVER['SCRIPT_FILENAME']); 
			$ruta = $doc_root ."/".$folderProject."/".$path.$entityName;
			 
			 
			if(!is_dir($ruta)){
				if(!mkdir($ruta, 0777, true)) {
					 
				}	else {
					$myfile = fopen($ruta."/SP_SELECT_ALL_ROWS_FILTER_".$entityName.".sql", "w") or die("Unable to open file!");
					$result = fwrite($myfile, $content3);
					fclose($myfile);
					return $result;						
					
				}						
			} else {
					$myfile = fopen($ruta."/SP_SELECT_ALL_ROWS_FILTER_".$entityName.".sql", "w") or die("Unable to open file!");
					$result = fwrite($myfile, $content3);
					fclose($myfile);
					return $result;					
			}		
			
		}		
		
		
		
		
		public function Test($model,$base){
			
			$s = new schema();
			$columnas = $s->Columns($model,$base);
			foreach($columnas as $columna){
				$data[] =  $columna['COLUMN_NAME'];
			}
			return  $data;
			
		}
		
	
	
	
		public static function GetView($viewName,$model=null){
			self::getPath();
			$resource = self::$viewPath.$viewName;
			
			
			if(file_exists($resource)){
				
				
				
				$content 	= file_get_contents($resource);
				
				
				$init 		= 		strrpos($content,"{") + 1;
				$end  		= 		strrpos($content,"}");
				$length     = 		$end - $init;				 
				$dynamic    =     	substr($content,$init,$length);	
				
				$res 		=  "";
				
				if($model!=null){
				 
					foreach($model as $key => $val){
						$res 		.= str_replace(array_keys($val),array_values($val),$dynamic);			
					}
					
					$content 		=    str_replace("{","",$content);
					$content 		=    str_replace("}","",$content);
					
					$view 		=    str_replace($dynamic,$res,$content);
				
					return $view;
					
				} else {
					
					return $content;
				}
				
				 
				 
			} else {
				echo $resource;
			}
		}	
	
	
	
		public function SetController($projectFolder,$model){
			 
			
			if(is_array($model)){
				$result = "";
				
				$file 		= __dir__ ."/Templates/CONTROLLER.TPL";
				
				$fileContent = file_get_contents($file);	

				
				$controllerName = $model['controllerName']."Controller";
				
				
				$content = str_replace("@Model",$model['modelName'],$fileContent);	
				
				$content0 = str_replace("@Entity",$model['entityName'],$content);	
				
				$content1 = str_replace("@Folder",$model['alias'],$content0);	

				$content2 = str_replace("@object",$model['instanceEntity'],$content1);
				
				$content3 = str_replace("@instance",$model['instanceModel'],$content2);
				
				$content4 = str_replace("@ControllerName",$controllerName,$content3);
				
				$content5 = str_replace("@alias",$model['alias'],$content4);
				
				$path = self::GetSettting("controller");
				 
			 
				$ruta = $path;
			 
				 
				$doc_root = preg_replace("!${_SERVER['SCRIPT_NAME']}$!", '', $_SERVER['SCRIPT_FILENAME']); 
				$ruta = $doc_root ."/".$projectFolder."/".$path;
				 
				 
				if(!is_dir($ruta)){
					 
					if(!mkdir($ruta, 0777, true)) {
						 
					}	else {
						$myfile = fopen($ruta."/".$model['controllerName']."Controller.php", "w") or die("Unable to open file!");
						$result = fwrite($myfile, $content5);
						fclose($myfile);
						return $result;						
						
					}						
				} else {
					$myfile = fopen($ruta."/".$model['controllerName']."Controller.php", "w") or die("Unable to open file!");
					$result = fwrite($myfile, $content5);
					fclose($myfile);
					return $result;	
				}				
				
			}
			
		}	
	
	
	
		//Se removio el argumento $path 26/04/2020 15:10
		public function SetDaoFrontEnd($projectFolder,$model,$controller){
			
			$doc_root = preg_replace("!${_SERVER['SCRIPT_NAME']}$!", '', $_SERVER['SCRIPT_FILENAME']); 
			
			$file 			= __dir__ ."/Templates/dao_js.TPL";
			$fileContent 	= file_get_contents($file);	
			$content1 		= str_replace("@Model",$model,$fileContent);	
			$content2 		= str_replace("@Controller",$controller,$content1);
			//$path 			= self::GetSettting("js/dao");
			
			$path = $doc_root ."/".$projectFolder ."/".self::GetSettting("js/dao");	
				 
			$myfile = fopen($path.$model.".js", "w") or die("Unable to open file!");
			$result = fwrite($myfile, $content2);
			fclose($myfile);
			return $result;			
		}



		
		public function SetPROCEDURE_DISPLAYBYID($folderProject,$dbName,$entityName){
			
			$db = new Database();
			$schema = new Schema('swapp');
			 
			$result = "";
			
			$file 		= __dir__ ."/Templates/getbyid_row_display_row.TPL";
			
			$fileContent = file_get_contents($file);	

			//$fields = $db->getProperties($dbName,$entityName);
			$fields = $schema->Columns($entityName,$dbName);
			 
			
			
			$fieldNames = array();
			
			foreach($fields as $field){
				if( $field['COLUMN_TYPE'] != 'DATETIME' || $field['COLUMN_TYPE'] != 'DATE'){
					$parameters[] = "i_".$field['COLUMN_NAME']."	".$field['COLUMN_TYPE'];
				} else {
					$parameters[] = "i_".$field['COLUMN_NAME']."	".$field['COLUMN_TYPE'];
				}
				
				
			}
			 
			foreach($fields as $field){
				$fieldNames[] = $field['COLUMN_NAME'];
			}
			
			foreach($fields as $field){
				$valores[] = "i_".$field['COLUMN_NAME'];
			}

			 

			$campos = 	implode(",",$fieldNames);
			
			 
			$entities = $schema->fromINNERjoin($entityName);
			 
			if(empty($entities)){ 
				$entities = $entityName;
			}
			
			$primaryKey = $schema->PrimaryKey($entityName,$dbName);
			$id = $primaryKey[0]['COLUMN_NAME'];
			
			$content = str_replace("@command","SP_DISPLAY_ROW_" . $entityName,$fileContent);	
			
			$content0 = str_replace("@entity",$entities,$content);	
			
			$content1 = str_replace("@fields",$campos,$content0);
			
			$content2 = str_replace("@id",$id,$content1);
	
			
			$path = self::GetSettting("sql");
			 
			$doc_root = preg_replace("!${_SERVER['SCRIPT_NAME']}$!", '', $_SERVER['SCRIPT_FILENAME']); 
			$ruta = $doc_root ."/".$folderProject."/".$path.$entityName;
			 
			 
			if(!is_dir($ruta)){
				if(!mkdir($ruta, 0777, true)) {
					 
				}	else {
					$myfile = fopen($ruta."/SP_DISPLAY_ROW_".$entityName.".sql", "w") or die("Unable to open file!");
					$result = fwrite($myfile, $content2);
					fclose($myfile);
					return $result;						
					
				}						
			} else {
					$myfile = fopen($ruta."/SP_DISPLAY_ROW_".$entityName.".sql", "w") or die("Unable to open file!");
					$result = fwrite($myfile, $content2);
					fclose($myfile);
					return $result;					
			}		
			
		}
		
		
		

		public function SetPROCEDURE_ADD_DOCUMENT($folderProject,$dbName,$parentName,$detailName){
			
			$db = new Database();
			 
			$result = "";
			
			$file 		= __dir__ ."/Templates/add_document.TPL";
			
			$fileContent = file_get_contents($file);	

			$parentFields = $db->getProperties($dbName,$parentName);
			
			$detailFields = $db->getProperties($dbName,$detailName);
			
			$parentFieldNames = array();
			
			
			
			foreach($parentFields as $field){
				if( $field['type'] != 'DATETIME' || $field['type'] != 'DATE'){
					$parentWorkVariables[] = "w_".$field['propertyName']."	".$field['type2'];
				} else {
					$parentWorkVariables[] = "w_".$field['propertyName']."	".$field['type'];
				}
			}
			
			
			foreach($parentFields as $field){
				$selectParentFieldNames[] = 'w_master->>"$.'.$field['propertyName'].'"';
			}
			 
			 
			foreach($parentFields as $field){
				$parentFieldNames[] = $field['propertyName'];
			}
						
			
			foreach($parentFields as $field){
				$parentValores[] = "w_".$field['propertyName'];
			}

			$masterWorkVariable 	=   implode(",\r\n",$parentWorkVariables);
			
			$selectMaster 			=   implode(",\r\n",$selectParentFieldNames);
	
			$parentCampos 			= 	implode(",\r\n",$parentFieldNames);
				
			$parentValues 			=   implode(",\r\n",$parentValores);


			$detailFieldNames = array();
			
			foreach($detailFields as $field){
				if( $field['type'] != 'DATETIME' || $field['type'] != 'DATE'){
					$detailWorkVariables[] = "w_".$field['propertyName']."	".$field['type2'];
				} else {
					$detailWorkVariables[] = "w_".$field['propertyName']."	".$field['type'];
				}
			}
			
			
			foreach($detailFields as $field){
				$selectDetailFieldNames[] = 'w_model->>"$.'.$field['propertyName'].'"';
			}
			 
			 
			foreach($detailFields as $field){
				$detailFieldNames[] = $field['propertyName'];
			}
						
			
			foreach($detailFields as $field){
				$detailValores[] = "w_".$field['propertyName'];
			}
			
			
			$detailWorkVariable 	=   implode(",\r\n",$detailWorkVariables);
			
			$selectDetail 			=   implode(",\r\n",$selectDetailFieldNames);
	
			$detailCampos 			= 	implode(",\r\n",$detailFieldNames);
				
			$detailValues 			=   implode(",\r\n",$detailValores);
			
			
			$content = str_replace("@command","SP_ADD_DOCUMENT_" . $entityName,$fileContent);

			/*
				master
			*/			
			
			$content0 = str_replace("@entity",$parentName,$content);	
			
			$content0 = str_replace("@masterWorkVariables",$masterWorkVariable,$content);	
			
			$content0 = str_replace("@selectMasterFields",$selectMaster,$content);	
			
			$content1 = str_replace("@masterFields",$parentCampos,$content0);	

			$content2 = str_replace("@masterValues",$parentValues,$content1);
			/*
				detail
			*/
			
			
			$content3 = str_replace("@detailEntity",$detailName,$content2);
			
			$content3 = str_replace("@detailWorkVariables",$detailWorkVariable,$content2);
			
			$content3 = str_replace("@selectDetailFields",$selectDetail,$content2);
			
			$content3 = str_replace("@detailFields",$detailCampos,$content2);
			
			$content3 = str_replace("@detailValues",$detailValues,$content2);
			
			$path = self::GetSettting("sql");
			 
			 
			$doc_root = preg_replace("!${_SERVER['SCRIPT_NAME']}$!", '', $_SERVER['SCRIPT_FILENAME']); 
			$ruta = $doc_root ."/".$folderProject."/".$path.$entityName;
			 
			 
			 
			
			if(!is_dir($ruta)){
				 
				if(!mkdir($ruta, 0777, true)) {
					 
				}	else {
					$myfile = fopen($ruta."/SP_ADD_DOCUMENT_".$entityName.".sql", "w") or die("Unable to open file!");
					$result = fwrite($myfile, $content3);
					fclose($myfile);
					return $result;						
					
				}						
			} else {
				$myfile = fopen($ruta."/SP_ADD_DOCUMENT_".$entityName.".sql", "w") or die("Unable to open file!");
				$result = fwrite($myfile, $content3);
				fclose($myfile);
				return $result;	
			}
			 


			
	
			
		}
		
	
	
	
		private static function SetTree(&$prototype,$model)
		{
			//Return an json object / row 
			/*
				//Collection to be formed
				[
					*
					*
					{				pattern				}
					*
					*	
				]
				//
			*/
			 
			 
	
			if(is_array($prototype)){
				 
				if(is_object($model)){
					
					foreach($prototype as $key => &$val){
						if($val["isDataModel"] == true){
							$val["model"] 		= $model;
							$name = (	new \ReflectionClass($model))->getShortName();
							 
							$val["modelName"] 	= (	new \ReflectionClass($model))->getShortName();
							 
							if($val["hasChilds"]==true){
								self::SetTree($val["Childs"],$model);
							}  
						} else if($val["isProperty"] == true){
							$temp = (array)$model;
							if(empty($val["propertyName"]) || $val["propertyName"]==''){
								//do nothing
							} else {
								//Asignar valor del campo
								$val["textNode"] = $temp[$val["propertyName"]];
							}
							
							
							 
						}
					}
					 
				}
			}
			
			return $prototype;
			
		}
	
	
	
		//Revision y uso 14/07/2020
		public function ReplicateElement($newEntityID,$newParentID,$entityID){
			
			$html = new HTML();
			$prototype 	= $html->getElementsJSON($entityID,0);	 	
			 
			$this->CreateElements(	$newEntityID,	$prototype,	$newParentID	);
			
		}
		
		
		
		//Revision y uso 14/07/2020
		private function CreateElements($entityID,$elements,$parentID){
			
			self::connectar();
			 
			foreach($elements as $key => $val){
				
				$sql = "call SP_REPLICATE_ELEMENT(:i_prototype_elementID,:i_new_entityID,:i_new_parentID);";
				$res = self::$conn->prepare( $sql);	
				$res->bindValue(":i_prototype_elementID", 	$val['elementID'],  PDO::PARAM_INT	);			
				$res->bindValue(":i_new_entityID", 			$entityID,  		PDO::PARAM_INT	);			
				$res->bindValue(":i_new_parentID", 			$parentID,  		PDO::PARAM_INT	);			
				$result = $res->execute();
				
				$row = $res->fetch(PDO::FETCH_OBJ);		
				if($row==false){
					return false;
				} else {
					self::$id = $row->id;	
				}				 
				if($val['hasChilds'] == true){
					$this->CreateElements($entityID,$val['Childs'],self::$id); 
				} else {
					
				}
			}
		}


        //creacion y revision 16/10/2020
		public static function readHTML($htmlFile){
			
			
			$viewPath = self::GetSettting('path');

			$resource 		= $viewPath.$htmlFile;
			 
			if(file_exists($resource)){
				 
				$htmlFile 	= 	fopen($resource, "r") or die("Unable to open file!");
				
				$selector_token_opening	= "";
				$selector_token_closing = "";				
				
				$temp_selector			= "";
				 
				//Etiqueta solamente 
				$init_tag 				= 0;
				$end_tag            	= 0;
				
				$tag_opening    		= 0;
				$tag_closing    		= 0;
					
				$about_to_open 			= 0;
				$about_to_close 		= 0;
					
				//Selector definido	
				$opening_selector		= 0;
				$closing_selector		= 0;
 
				$opening_token_taken 	= 0;
				$closing_token_taken 	= 0;
				
				$selector_attribs  = [];
				$selector_close    = [];
				
				$parentID		   = 0;
				
				 
				$id				   = 0;
				
				$currentID		   = 0;
				$currentLevel	   = 0;
				$lastLevel		   = 0;
				
				self::$pastLevel   = 0;
				//Level Tree
				$level				= 0;
				
				//Caracter por caracter
				while(!feof($htmlFile)) {
					 
					//Es una etiqueta de apertura
					$token = fgetc($htmlFile);
					 
					if($token == "<"){
						$init_tag 	= 1;
						$end_tag 	= 0;
					}
					
					if($token == ">"){
						$init_tag 	= 0;
						$end_tag 	= 1;
					}			 
						
						
						
					//Selector de apertura 
					if( $init_tag ==1 && $end_tag == 0 ){
						if($token!="<"){
							$temp_selector .= $token;	
						}
						
					}  
					
				 
					//Selector de cierre  				 
					if($init_tag==0 && $end_tag==1){
						
						//Etiqueta de Cierre
						if (strstr($temp_selector, '/') !== false) {
							if(strlen($temp_selector) > 0 ){
								
								$selector_close[] 	= array(
									"select_close" 		=> ltrim($temp_selector,"/"),
									"id"			 	=> $id,
									"parentID"		 	=> $parentID,
									"level"			 	=> $level
								);
								
								$level = $level - 1;
							}
							
						} else {
							//Etiqueta de apertura
							if(strlen($temp_selector) > 0 ){
								
								//Nuevo id
								$id++;
								//Nuevo nivel
								$level = $level + 1;
								
								$currentLevel = $level;
								//echo $level.":".$currentLevel.":".$lastLevel."\n";
								
								$_temp = explode(" ",$temp_selector);
									
								$selector_attribs[] = array( 
									"select_attribs" 	=> 	$temp_selector,
									"id"			 	=> 	$id,
									"parentID"		 	=> 	$parentID,
									"level"			 	=> 	$level									
								);
								
								if(is_array($_temp)){
									 
									//void tag elements 
									$voidTag = trim($_temp[0]);
									switch($voidTag){
										case 'img':
										$level = $level - 1;
										break;
										case 'input':
										$level = $level - 1;
										break;
										case 'hr':
										$level = $level - 1;
										break;	
										case 'br':
										$level = $level - 1;
										break;
										case 'link':
										$level = $level - 1;
										break;
										case 'meta':
										$level = $level - 1;
										break;			
										case 'param':
										$level = $level - 1;
										break;										
									}
									
								}								
								
							}
						}
						
						$temp_selector = ""; 
						 
					} 
					

					 
					 
				}				
				 
				
				 
				
				return  $selector_attribs;
			 
				
				fclose($htmlFile);		

				
	 
			} else {
				 
			}			
		}
		
		
       //creacion y revision 16/10/2020
		public static function readHTMLstring($strHTML){
			
			
			$viewPath = self::GetSettting('path');

			$resource 		= $viewPath."Templates/template.html";
			 
			 
			if(file_exists($resource)){
				
				$strFile = fopen($resource, "w") or die("Unable to open file!");
				
				fwrite($strFile, $strHTML);
			
				fclose($strFile);				
				 
				$htmlFile 	= 	fopen($resource, "r") or die("Unable to open file!");
				
				$selector_token_opening	= "";
				$selector_token_closing = "";				
				
				$temp_selector			= "";
				 
				//Etiqueta solamente 
				$init_tag 				= 0;
				$end_tag            	= 0;
				
				$tag_opening    		= 0;
				$tag_closing    		= 0;
					
				$about_to_open 			= 0;
				$about_to_close 		= 0;
					
				//Selector definido	
				$opening_selector		= 0;
				$closing_selector		= 0;
 
				$opening_token_taken 	= 0;
				$closing_token_taken 	= 0;
				
				$selector_attribs  = [];
				$selector_close    = [];
				
				$parentID		   = 0;
				
				 
				$id				   = 0;
				
				$currentID		   = 0;
				$currentLevel	   = 0;
				$lastLevel		   = 0;
				
				self::$pastLevel   = 0;
				//Level Tree
				$level				= 0;
				
				//Caracter por caracter
				while(!feof($htmlFile)) {
					 
					//Es una etiqueta de apertura
					$token = fgetc($htmlFile);
					 
					if($token == "<"){
						$init_tag 	= 1;
						$end_tag 	= 0;
					}
					
					if($token == ">"){
						$init_tag 	= 0;
						$end_tag 	= 1;
					}			 
						
						
						
					//Selector de apertura 
					if( $init_tag ==1 && $end_tag == 0 ){
						if($token!="<"){
							$temp_selector .= $token;	
						}
						
					}  
					
				 
					//Selector de cierre  				 
					if($init_tag==0 && $end_tag==1){
						
						//Etiqueta de Cierre
						if (strstr($temp_selector, '/') !== false) {
							if(strlen($temp_selector) > 0 ){
								
								$selector_close[] 	= array(
									"select_close" 		=> ltrim($temp_selector,"/"),
									"id"			 	=> $id,
									"parentID"		 	=> $parentID,
									"level"			 	=> $level
								);
								
								$level = $level - 1;
							}
							
						} else {
							//Etiqueta de apertura
							if(strlen($temp_selector) > 0 ){
								
								//Nuevo id
								$id++;
								//Nuevo nivel
								$level = $level + 1;
								
								$currentLevel = $level;
								//echo $level.":".$currentLevel.":".$lastLevel."\n";
								
								$_temp = explode(" ",$temp_selector);
									
								$selector_attribs[] = array( 
									"select_attribs" 	=> 	$temp_selector,
									"id"			 	=> 	$id,
									"parentID"		 	=> 	$parentID,
									"level"			 	=> 	$level									
								);
								
								if(is_array($_temp)){
									 
									//void tag elements 
									$voidTag = trim($_temp[0]);
									switch($voidTag){
										case 'img':
										$level = $level - 1;
										break;
										case 'input':
										$level = $level - 1;
										break;
										case 'hr':
										$level = $level - 1;
										break;	
										case 'br':
										$level = $level - 1;
										break;
										case 'link':
										$level = $level - 1;
										break;
										case 'meta':
										$level = $level - 1;
										break;			
										case 'param':
										$level = $level - 1;
										break;										
									}
									
								}								
								
							}
						}
						
						$temp_selector = ""; 
						 
					} 
					

					 
					 
				}				
				 
				
				 
				
				return  $selector_attribs;
			 
				
				fclose($htmlFile);		

				
	 
			} else {
				 
			}			
		}		
		
		
		
		public static function CreateDomElements($strHMTL, $entityID){
			
			
			$elements 	= self::readHTMLstring($strHMTL);
			 
		
			$lastLevel 		= 0;
			$currentID  	= 0;
			$lastID     	= 0;
			$iCounter   	= 1;
		
			$lastParentID 	= 0;
			
			$parentsID		= [];
			
			$currentKey      = 0;
			
			//Init parentID = 0
			
			
			foreach($elements as &$element){
				
				if($iCounter==1){
					$lastID = $element['id'];
					$parentsID[$element['level']] = 0;
				}   
			
				$currentID = $element['id'];
				
				if(trim($lastLevel) == trim($element['level'])){
					 
					$element['parentID'] 			= $lastParentID;	
					
					//Mismo nivel, mismo padre
					$parentsID[$element['level']] 	= $lastParentID;
					$lastID    = $currentID;
					//Actualizar
					$lastLevel = $element['level'];				
				} else {
					
					
					
					//Son niveles diferentes
					if($iCounter==1){
						$element['parentID'] 	= 0;
					} else {
						$lastParentID  			= $lastID;		
					}          
					
					  
					
					if(trim($element['level'] == 1)){
						//Reset
						$lastParentID 			= 	0;
						$currenKey				=   0;
					}
				
					//Es mayor > el arbol va hacia lo profundo
					if($element['level'] > $lastLevel){
						$parentsID[$element['level']] 	= $lastParentID;
						//Va acuerdo al nivel que va descendiendo en el arbol
						$element['parentID'] 			= $lastParentID;
					}
					
					//Es menor < el arbol va hacia lo superior
					if($element['level'] < $lastLevel){
						//Va acuerdo a lo guardado en el array porque va ascendiendo en el arbol
						$element['parentID'] 			= $parentsID[$element['level']];
						
						
					}				
					
					$lastID 		   	= $currentID;
					//Actualizar
					$lastLevel = $element['level'];
				}
				
				$iCounter++;
				

			}
			
			$dao_element 	= new Elemento();
			$dao_type 		= new html_type();
		
			
			
			foreach($elements as $el){
				
					$model 		= new html_element();
					 
					 
					 
					$select_attribs =  explode(" ",$el['select_attribs']);
					 
					$filter 		= trim($select_attribs[0]);
					
					$attribs = [];
					$iCount  = 1; 
					
					if(count($select_attribs) >= 1){
						
						$newString = "<".$select_attribs[0];
						foreach($select_attribs as $ea){
							if($iCount > 1 ) {
								$newString .= " ".$ea;
							}
							
							$iCount++;	
						}
						
						if($select_attribs[0] == "img"){
							//Sin etiqueta de cierre
							$newString .=">";
						} else {
							//Con etiqueta de cierre
							$newString .="></".$select_attribs[0]  .">";
						}
						
						 
						libxml_use_internal_errors(true);
						$dom = new \DOMDocument();
						$resultado = $dom->loadHTML($newString);
						

						$de = $dom->getElementsByTagName($select_attribs[0])->item(0);
						
						
						if ($de->hasAttributes()) {
						  foreach ($de->attributes as $attr) {
							  $attribs[] = array(
								"attribName"  => $attr->nodeName,
								"attribValue" => $attr->nodeValue
							);
						  }
						}	
	 
					}
					
					$dao_type->filter 	= $filter;
					$rows 				= $dao_type->getRowsFilter();
	 
					 
					 
					$typeID 	=   $rows[0];
				 
					
					if(isset($typeID)){
						$elementTypeID = $typeID['id'];
					} else {
						$elementTypeID = 0;
					}
					 
					$parentID = 0; 
					if($el['id'] != 1){
						$parentID = 100 + $el['parentID']	; 
					} 
					 
					$model->id					=   100 + $el['id'];
					$model->entityID 			= 	$entityID;
					$model->isDataModel			= 	0	;
					$model->isProperty			= 	0	;
					$model->elementTypeID		= 	$elementTypeID	;	
					$model->parentID			= 	$parentID;			
					$model->absoluteOrder		= 	0;			
					$model->relativeOrder		= 	0;			
					$model->fieldName			= 	"";			
					$model->level				= 	$el['level']	;			
					$model->contentHTML			= 	""	;			
					$model->globalParentID		= 	0;			
					$model->entityView			= 	0;			
					$model->hasChilds			= 	0;			
					$model->isDataContainer		= 	0;		

					
					$dao_element->model = $model;
					
					//$newObject = $dao_element->addRow();
					$result = $dao_element->addRow();
					 
					
					
					//if(gettype($newObject)=='object'){
						 
						
						$properties = new html_property();
						
						 
						
						$daoProperty = new ElementoPropiedad();
						
						foreach($attribs as $attrib){
							//Get property id
							$properties->filter = $attrib['attribName']; 
							$propertiesSet 		= $properties->getCollectionsFilter()->model;
							 
							if(gettype($propertiesSet)=='object'){
								$propertyID = $propertiesSet->id;
							} else {
								echo $attrib['attribName']."<br>";
								 
							}
							
							$eProperty  				= new html_elementproperty(); 
							$eProperty->id				= 0 ;
							$eProperty->elementID	    = $model->id; 
							$eProperty->propertyID      = $propertyID;
							$eProperty->propertyValue	= $attrib['attribValue'];
							
							$daoProperty->model = $eProperty;
							$daoProperty->addRow();
							 
						}

						
					//}
				
			}			
			
		}


}



?>