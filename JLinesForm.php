<?php
/**
 * Widget para Generar Lineas para un formulario de table detalle o para entradas Tabulares
 * Necesitas tener Instalado el bootstrap ya que se usan varios 
 * widgets de esta extension
 * 
 *
 * Usado de Yii-Playground - Dynamics Row pero mejorado el uso
 * @link http://www.eha.ee/labs/yiiplay/index.php/en/site/extension?view=dynamicrows
 * 
 * @author Juan David Rodriguez R. <jdrodrigez429@gmail.com> @juanda1015
 * @copyright 2013 - Juan David Rodriguez Ramirez
 * @license New BSD License
 * @category User Interface
 */
class JLinesForm extends CWidget{
        /**
         * Tipos validos para los $elementsPreCopy
         * @var array
         * @access private
         * @static
         */
        private static $VALIDTYPES = array('hiddenField','textField','fileField',/*'textArea',*/'uneditable','checkBox','radioButton');
        /**
         * Tipos validos para los $elementsPreCopy con items
         * @var array
         * @access private
         * @static
         */
        private static $VALIDTYPESITEMS = array('dropDownList','checkBoxListInline','checkBoxList','radioButtonList');
        /**
         * Eventos jQuery validos para los campos
         * @var array
         * @access private
         * @static
         */
        private static $VALIDEVENTS = array('change','click','dbclick','focus','blur');
        /**
         * Widget validos para los campos
         * @var array
         * @access private
         * @static
         */
        private static $VALIDWIDGETS = array(
                            'zii.widgets.jui.CJuiDatePicker'=>'datepicker',
                            'zii.widgets.jui.CJuiAutoComplete'=>'autocomplete',
                            'CMaskedTextField'=>'mask',
                            'ext.chosen.Chosen'=>'chosen',
                        );
        /**
         * Url de la carpeta asstes dentro de extensions
         * @access private
         * @var string $_assets
         */
        private $_assets ;
        /**
         * Id que contendra la class .add
         * @var string  $_idAdd
         */
        protected $_idAdd;
        /**
         * Id que contendra el boton de Nuevo
         * @var string  $_idAdd
         */
        protected $_idNew;
        /**
         * Id que contendra el boton Eliminar
         * @var string  $_idAdd
         */
        protected $_idDelete;
        /**
         * Id que contendra la class .remove
         * @var string  $_idAdd
         */
        protected $_idRemove;
        /**
         * Id que contendra la class .edit
         * @var string  $_idAdd
         */
        protected $_idEdit;
        /**
         * Modelo para las Lineas
         *
         * @var CActiveRecord $model
         */
        public $model;
        /**
         * Objeto Contenedor del Formulario
         *
         * @var CActiveForm $form
         */
        public $form;
        /**
         * Attributo para saber que numero de linea colocar en las HtmlOptions que se necesitan
         * @var string 
         */
        private $_numLines = '{0}';
        /**
         * Opciones HTML para el boton de agregar linea
         * ya que aveces se necesita que este boton tenga 
         * ciertos atributos html para manejar mediante eventos jQuery
         * 
         * @var array  $htmlAddOptions
         */
        public $htmlAddOptions = array();
        /**
         * Opciones HTML para el boton de Actualizar linea
         * @access private
         * @var array  $htmlUpdateOptions
         * @access protected
         */
        private $htmlUpdateOptions;
        /**
         * Opciones HTML para el boton de Eliminar linea
         * @access private
         * @var array  $htmlDeleteOptions
         * @access protected
         */
        private $htmlDeleteOptions;
        /**
         * Elementos que se van a copiar
         * @var array $elementsPreCopy
         */
        public $elementsPreCopy = array();
        /**
         * Elementos que van en la tabla de lineas
         * @var array $elementsCopy
         */
        public $elementsCopy = array();
        /**
         * Flag para saber a que vista dirigir,
         * si a la vista "lines" opciones con dobleclick y modal
         * o a la vista "lines_edit" solo editando campos en la tabla
         * @var boolean $editInline
         */
        public $editInline = true;
        /**
         * Atributos en forma $key=>$value para colocar en findByAttributes();
         * @var arrray $searchAttributes
         */
        public $searchAttributes = array();
        /**
         * Attributo para saber si se deben mostrar los elementos guardados en la base de datos 
         * de este modelo o no
         * @var boolean  $showElementsSaved por defecto true
         */
        public $showElementsSaved = true;
        /**
         * Attributo para almacenar el codigo js que se debe ejecutar despues que se haga click en el boton nuevo
         * @var string  $jsAfterCopy
         */
        private $jsAfterCopy = '';
        
        /**
         * Inicializar Widget
         */
        public function init() {
            if(!isset($this->form))
                    throw new Exception('Se debe definir la propiedad "form" en la llamada al widget');
            if(!isset($this->model))
                    throw new Exception('Se debe definir la propiedad "model" en la llamada al widget');
            //Asignamos los ids correspondientes
            if(isset($this->htmlAddOptions['id']))
                 $this->_idNew = $this->htmlAddOptions['id'];
            else{
                $this->_idNew = 'n'.$this->getId();
                $this->htmlAddOptions['id'] = $this->_idNew;
            }
            $this->_idAdd = $this->getId();
            $this->_idDelete = 'd_'.$this->getId();
            $this->_idRemove = 'r_'.$this->getId();
            $this->_idEdit = 'e_'.$this->getId();
            
            //inicializamos htmlOptions de botones
            $this->htmlDeleteOptions = array('id'=>$this->_idDelete.'_'.$this->_numLines,'class'=>'delete_'.$this->_idDelete,'name'=>$this->_numLines);
            $this->htmlUpdateOptions = array('id'=>$this->_idEdit.'_'.$this->_numLines,'class'=>'update_'.$this->_idEdit, 'name'=>$this->_numLines);
                        
            $this->render($this->editInline ? 'lines_edit':'lines');
            
            $this->registerScripts();
            
            parent::init();
        }
        /**
         * Este metodo registrara los js 
         * Nesesarios a usar y algun codigo extra
         * que se necesite
         */
        protected function registerScripts(){
            
            $js = Yii::app()->clientScript;
            
            $this->_assets = Yii::app()->assetManager->publish(dirname(__FILE__).DIRECTORY_SEPARATOR.'assets');
            $js->registerCoreScript('jquery');
            $js->registerScriptFile($this->_assets .'/js/jquery.calculation.min.js');
            $js->registerScriptFile($this->_assets .'/js/jquery.format.js');
            $js->registerScriptFile($this->_assets .'/js/JLinesForm.js');
            
            $js->registerScript($this->_idAdd,' 
                //JlinesForm para model "'.get_class($this->model).'"
                $(function(){
                    jQuery("#'.$this->_idNew.'").click(function(){
                        jQuery("#'.$this->_idAdd.'").click();
                        var place = jQuery(this).parents(".templateFrame:first").children(".templateTarget");
                        var row = place.find(".rowIndex").max();
                        '.$this->jsAfterCopy.'
                    });
                    jQuery(".delete_'.$this->_idDelete.', .deleteLine_'.$this->getId().'").live("click",function(){
                        var idRow = jQuery(this).attr("name");
                        var model = "'.get_class($this->model).'";
                        var deleteHidden = jQuery("#"+model+"_delete").val()
                        var idFieldDelete = jQuery("#"+model+"_"+idRow+"_'.$this->model->tableSchema->primaryKey.'").val();

                        deleteHidden = deleteHidden + idFieldDelete +",";
                        jQuery("#"+model+"_delete").val(deleteHidden);

                        jQuery("#'.$this->_idRemove.'_"+$(this).attr("name")).click();

                        //Reorganizamos los ids y names
                        var place = jQuery(this).parents(".templateFrame:first").children(".templateTarget");
                        var countMax = place.find(".rowIndex").max();
                        var countFor = parseInt(idRow, 10)+1;
                        var line = parseInt(idRow, 10); 
                        for(var i = countFor ; i <=countMax; i++){
                            var fields = '.$this->getFields().';
                            var elements = '.$this->getElements().';
                            for(var x =0 ; x<=elements.length;x++){
                                if(elements[x] == "'.$this->getId().'_rowIndex")
                                    jQuery("#"+elements[x]+"_"+i).val(line);
                                jQuery("#"+elements[x]+"_"+i).attr({
                                    id: elements[x]+"_"+line,
                                    name: line
                                });
                            }
                                
                            for(var y =0 ; y<=fields.length;y++){
                                jQuery("#"+model+"_"+i+"_"+fields[y]).attr({
                                   id: model+"_"+line+"_"+fields[y],
                                   name: model+"["+line+"]["+fields[y]+"]"
                               });
                           }
                           line++;
                        }
                    });
                    '.$this->getEvents().'
                });                
               '
           );
        }
        /**
         * Metodo para organizar los eventos jQuery que van a tener los campos
         * @return string $result
         */
        private function getEvents(){
            $result = '';
            $continue = true;
            foreach($this->elementsCopy as $element=>$options){
                foreach($options as $key=>$option){
                    if(is_int($key)&& is_array($option) && isset($option['event']) && $option['name']){
                        foreach($option['event'] as $event=>$function){
                            if(in_array($event, self::$VALIDEVENTS)){
                                $object = strtolower($option['name']).'_'.$this->getId();
                                $this->elementsCopy[$element][$key]['htmlOptions']['class'] = isset($option['htmlOptions']['class']) ? $option['htmlOptions']['class'] : $object.'_class';
                                $class = $this->elementsCopy[$element][$key]['htmlOptions']['class'] ;
                                $result .= '
                                    $(".'.$class.'").live("'.$event.'",function(){
                                        var '.$object.' = '
                                            .$function.
                                        ';
                                        '.$object.'($(this),$(this).attr("id").split("_")[1]);
                                     });
                                ';
                            }else{
                                $continue = false;
                            }
                        }
                    }
                }
                if(isset($options['event']) && $continue){
                    foreach($options['event'] as $event=>$function){
                        if(in_array($event, self::$VALIDEVENTS)){
                            $object = strtolower($element).'_'.$this->getId();
                            $this->elementsCopy[$element]['htmlOptions']['class'] = isset($options['htmlOptions']['class']) ? $options['htmlOptions']['class'] : $object.'_class';
                            $class = $this->elementsCopy[$element]['htmlOptions']['class'];
                            $result .= '
                                $(".'.$class.'").live("'.$event.'",function(){
                                    var '.$object.' = '
                                        .$function.
                                    ';
                                    '.$object.'($(this),$(this).attr("id").split("_")[1]);
                                 });
                            ';
                        }
                    }
                }
            }
            return $result;
        }
        /**
         * Metodo para Retornar los elementos alternos a los campos
         * que se deben cambiar al momento de eliminar una linea
         */
        private function getElements(){
            $elements = array(
                $this->_idRemove,
                $this->_idDelete,
                $this->getId()."_rowIndex",
            );
             return CJavaScript::encode($elements);
            
        }
        /**
         * Metodo para retornar los names de los campos de la base de datos
         * @return string names de los campos de la base de datos
         */
        private function getFields(){
            $fields = array();
            foreach($this->model->attributeNames() as $attribute){
                if(array_key_exists($attribute, $this->elementsCopy))
                        $fields[]=$attribute;
            }
            return CJavaScript::encode($fields);
        }
        /**
         * Este metodo se encarga de validacion de las lineas,
         * tanto para el metodo editInlite true o false
         * @param mixed $modelLines Puede ser CModel con objeto de un solo modelo o un array con varios (para cuando usa varias veces el widget)
         * @param string $idForm El id del formulario que esta usando el widget
         * @static
         */
        public static function validate($modelLines,$idForm){
             // array que tendra los modelos a ser validados
             $models=array();
             //Armamos el array de models para validar
             if(is_array($modelLines)){
                 foreach($modelLines as $model){
                     if(isset($_POST[get_class($model)])){
                             $models[]=$model;
                     }
                 }
             }elseif(isset($_POST[get_class($modelLines)])){
                           $models[]=$modelLines;
             }
             //verifica que haya una peticion ajax para validar JLinesModel
             if(isset($_POST['ajax']) && $_POST['ajax']===$idForm){
                   if(isset($_POST['JLinesModel'])){
                       // Instanciamos el Model Clonado y asignamos propiedades
                         $JLinesModel = new JLinesModel;
                         $JLinesModel->rules = $modelLines->rules();
                       //asignamos lo valores del post al modelo
                       foreach($_POST['JLinesModel'] as $name=>$value)
                            $JLinesModel->$name = $value;
                       //Finalmente mostramos los errores de validacion
                        echo CActiveForm::validate($JLinesModel);
                        Yii::app()->end();
                   }
             }
             if(isset($_POST['jlines']) && $_POST['jlines']===$idForm)
             {     
                 echo self::validateTabular($models);
                 Yii::app()->end();
             }
            
        }
        /**
         * Valida un array con instancias del modelo y retorna los resultados en formato JSON.
         * Basado en CActiveForm::validateTabular();
         * 
	 * @param mixed $models array con instacias del modelo o un solo modelo a validar
         * 
         * @return string JSON Representando los mensajes de validacion.
         */
        public static function validateTabular($models){
            $result = array();
            if(!is_array($models))
		$models=array($models);
            foreach($models as $i=>$model)
            {
                if(isset($_POST[get_class($model)]))
                {
                    foreach($_POST[get_class($model)] as $count=>$data)
                    {
                        if(is_int($count))
                        {
                            $model->attributes=$_POST[get_class($model)][$count];
                            $model->validate();
                            foreach($model->getErrors() as $attribute=>$errors)
                                   $result[CHtml::activeId($model,'['.$count.']'.$attribute)]=$errors;

                            $model->unsetAttributes();
                        }
                    }
                }
                
            }
            return function_exists('json_encode') ? json_encode($result) : CJSON::encode($result);
        }
        /**
         * Metodo encargado de guardar en los modelos los elementos que vengan por post
         * @param mixed $modelLines Puede ser CModel con objeto de un solo modelo o un array con varios (para cuando usa varias veces el widget)
         * @param array $staticValues array indexado para los valores que son siempre los mismos, en caso de maestro detalle o cuando necesito un valor siempre al guardar igual indicar asi:
         *                            array(
         *                              get_class($detalle)=>array(
         *                                  'maestro'=>$maestro->primaryKey
         *                              ),
         *                              get_class($tabulares)=>array(
         *                                  'campo'=>'Estatico'
         *                              )
         *                            )
         * @param array $deleteOptions array indexado en caso de que manejen un borrado logico y se necesite cambiar atributos al eliminar un registro:
         *                            array(
         *                              get_class($model)=>array(
         *                                  'activo'=>'N'
         *                                  'actualizado_por'=>'admin'
         *                              ),
         *                              get_class($model2)=>array(
         *                                  'activo'=>'N'
         *                                  'actualizado_por'=>'admin'
         *                              ),
         *                            )
         * @static
         */
        public static function save($modelLines,$staticValues = NULL,$deleteOptions = NULL){
            // array que tendra los modelos a ser guardados
             $models=array();
             //Armamos el array de models para guardar
             if(is_array($modelLines)){
                 foreach($modelLines as $data)
                             $models[]=$data;
             }elseif(isset($_POST[get_class($modelLines)])){
                           $models[]=$modelLines;
             }
             //Ahora guardamos cada modelo
             foreach($models as $model) {
                $nameModel = get_class($model);
                if(isset($_POST[$nameModel])){
                    foreach($_POST[$nameModel] as $count=>$data){
                        if(isset($_POST[$nameModel][$count])){
                            $model = new $nameModel;
                            if(isset($_POST[$nameModel][$count][$model->tableSchema->primaryKey])){
                                $query = $model->findByPk($_POST[$nameModel][$count][$model->tableSchema->primaryKey]);
                                if($query)
                                    $model = $query;
                                else
                                    $model = new $nameModel;
                            }
                            
                            foreach($data as $name=>$value)
                                $model->$name=$value;
                            
                            foreach($model->attributes as $name=>$value){
                                if($value == '')
                                    $model->$name = NULL;
                            }
                            if(isset($staticValues[$nameModel])){
                                foreach($staticValues[$nameModel] as $name=>$value)
                                    $model->$name = $value;
                            }
                            $model->save();
                        }
                    }
                }
                if(isset($_POST[$nameModel.'_delete'])){
                    $arrayDelete = explode(',',$_POST[$nameModel.'_delete']);
                    foreach($arrayDelete as $delete){
                        if(!isset($deleteOptions))
                            $model->deleteByPk($delete);
                        else{
                            $model->updateByPk($delete,$deleteOptions[$nameModel]);
                        }
                            
                    }
                }

            }
        }
        /*
         * Metodo para retornar el contador
         * que debe ir en el count de el footer de la tabla 
         */
        protected function getCountColspan(){
            return count($this->elementsCopy)+1;
        }
        /**
         * Metodo para retornar un Boton de Nuevo en Lineas
         * @return TbButton 
         */
       protected  function getButtonAddLine(){ 
                
                return $this->widget('bootstrap.widgets.TbButton', array(
                                      'buttonType'=>'button',
                                      'size'=>'normal',
                                      'type'=>'success',
                                      'label'=>isset($this->htmlAddOptions['label']) ? $this->htmlAddOptions['label'] : '',
                                      'icon'=>'plus white',
                                      'htmlOptions'=>$this->htmlAddOptions,
                            ),true);
                
                
        }
        /**
         * Metodo para retornar un Boton de Actualizar en Lineas
         * @param string $string si es un string o no
         * @return TbButton 
         */
        protected  function getButtonUpdateLine($string = false){
            return $this->widget('bootstrap.widgets.TbButton', array(
                                  'buttonType'=>'button',
                                  'size'=>'small',
                                  'icon'=>'pencil',
                                  'htmlOptions'=>$this->htmlUpdateOptions,
                            ),$string);
                
            
        }
        /**
         * Metodo para retornar un Boton de Eliminar en Lineas
         * @param string $string 
         * @return TbButton
         */
        protected  function getButtonDeleteLine($string = false){
            return $this->widget('bootstrap.widgets.TbButton', array(
                                  'buttonType'=>'button',
                                  'size'=>'small',
                                  'type'=>'danger',
                                  'icon'=>'minus white',
                                  'htmlOptions'=>$this->htmlDeleteOptions,
                            ),$string);
                
            
        }
              
        /**
         * Metodo para retornar los elementos que se van a copiar
         */
        protected function renderElementsPreCopy(){
            if(is_array($this->elementsPreCopy)){
                $countElements = count($this->elementsPreCopy) -1;
                $cont =0;
                //Recorremos los elementos y asignamos valores en caso de vacios
                foreach($this->elementsPreCopy as $element=>$options){
                    echo '<td style="width: auto">'.$this->createElement($element, $options,'elementPreCopy').'</td>';
                    if($cont == $countElements)
                        echo '<td style="width: auto">'.$this->getButtonAddLine().'</td>';
                    $cont++;
                }
            }
            
        }
        /**
         * Muestra los <th> de las tablas de las lineas
         * con los labels definidos en el widget
         * o el que tenga el atributo en el modelo
         */
        protected function renderHeaders(){
            if(is_array($this->elementsCopy)){
                echo '<thead>';
                    foreach($this->elementsCopy as $attribute=>$options){
                        if(isset($options['label']))
                            echo '<th><strong>'.CHtml::label($options['label']).'</strong></th>';
                        elseif(!isset($options['isModel']) ||  $options['isModel']==true)
                            echo '<th><strong>'.$this->form->labelEx($this->model,$attribute).'</strong></th>';
                    }
                    echo '<th></th>';
                echo '</thead>';
            }
        }
        
        /**
         * Metodo encargado de mostrar los elementos de la base de datos
         * ya guardados de este modelo y teniendo como opcion el $searchAttributes
         * para cargar los datos segun la condicion que necesite
         */
        protected function renderElementsSaved(){
            $search = $this->model->findAllByAttributes($this->searchAttributes);
            if(is_array($this->elementsCopy)){
                foreach($search as $i=>$model){
                    $this->model = $model;
                    echo '<tr class="templateContent">';
                        if(!array_key_exists($this->model->tableSchema->primaryKey, $this->elementsCopy))
                               echo $this->form->hiddenField($model,"[$i]".$this->model->tableSchema->primaryKey);
                        $this->_numLines = $i;
                        foreach($this->elementsCopy as $element=>$options){
                            if(isset($options['class']))
                                echo '<td style="width: auto">'.$this->getController()->widget($options['class'],$this->getOptionsWidget($element, $options, true),true).'</td>'; 
                            else
                                echo '<td style="width: auto">'.$this->createElement($element, $options).'</td>';
                        }
                        $this->htmlDeleteOptions['class']='delete_'.$this->_idDelete.' deleteLine_'.$this->getId();
                        $this->htmlDeleteOptions['id']=$this->_idDelete.'_'.$this->_numLines;
                        $this->htmlDeleteOptions['name']=$this->_numLines;
                        $this->renderActionButtons();
                    echo '</tr>';
               }
               $this->htmlDeleteOptions['class']='delete_'.$this->_idDelete;
               $this->_numLines = '{0}';
               $this->htmlDeleteOptions['id']=$this->_idDelete.'_'.$this->_numLines;
               $this->htmlDeleteOptions['name']=$this->_numLines;
               $this->model->unsetAttributes(); 
            }
        }
        /**
         * Metodo para mostrar el contenido del template
         * que se va a tener en cuenta al momento de la clonacion 
         * a causa del botoon (.add)
         * @access protected
         * 
         */
        protected function renderElementsTemplate(){
            if(is_array($this->elementsCopy)){
                
                //Recorremos los elementos y asignamos valores en caso de vacios
                foreach($this->elementsCopy as $element=>$options)
                     echo '<td style="width: auto">'.$this->createElement($element, $options).'</td>';
                
                $this->renderActionButtons();
                
            }
        }
        /**
         * Metodo para retornar las opciones que debe tener un widget sin el index "class"
         * @param string $element
         * @param array $options
         * @param boolean $saved
         * @return array $options Opciones oficiales del widget
         */
        private function getOptionsWidget($element,$options,$saved = false){
            $optiosWidget = array();
            foreach($options as $name => $value){
                if($name !== 'class' && $name !== 'type')
                    $optiosWidget[$name] = $value;
            }
            if(!isset($options['name']) && $saved){
                $optiosWidget['model'] = $this->model;
                $optiosWidget['attribute'] = "[{$this->_numLines}]$element";
            }else
                $optiosWidget['name'] = $element.'_'.$this->getId();
            return $optiosWidget;
        }
        /**
         * Metodo para construir los widgets que se van a usar posteriormente
         */
        protected function renderElementsWidgets(){
            if(is_array($this->elementsCopy)){
                
                //Recorremos los elementos y verificamos cuales tienen el index "class" para su creacion de widget
                foreach($this->elementsCopy as $element=>$options){
                    if(isset($options['class']) && array_key_exists($options['class'], self::$VALIDWIDGETS)){
                        $this->getController()->widget($options['class'],$this->getOptionsWidget($element,$options));
                        switch(self::$VALIDWIDGETS[$options['class']]){
                            case 'datepicker':
                                $this->jsAfterCopy .= 'jQuery("#'.get_class($this->model).'_"+row+"_'.$element.'").'.self::$VALIDWIDGETS[$options['class']].'(jQuery.extend({showMonthAfterYear:false},jQuery.datepicker.regional["'.$options['language'].'"],'.CJavaScript::encode($options['options']).')); ';
                            break;
                            case 'mask':
                                $this->jsAfterCopy .= 'jQuery("#'.get_class($this->model).'_"+row+"_'.$element.'").'.self::$VALIDWIDGETS[$options['class']].'("'.$options['mask'].'"); ';
                            break;
                            default:
                                $this->jsAfterCopy .= 'jQuery("#'.get_class($this->model).'_"+row+"_'.$element.'").'.self::$VALIDWIDGETS[$options['class']].'(); ';
                            break;
                        }
                    }
                }
                                                
            }
        }
        /**
         * Metodo para mostrar los botones a usar
         * en las acciones de la linea
         * @access private
         */
        private function renderActionButtons(){
            if($this->editInline){
                echo '<td style="width: 47px;padding-top: 20px;"> 
                                      <div class="remove" id ="'.$this->_idRemove.'_'.$this->_numLines.'"></div>
                                      <div style="float: left; margin-left: 5px;">'.$this->getButtonDeleteLine(true).'</div>'
                                      .CHtml::hiddenField($this->getId()."_rowIndex_$this->_numLines",$this->_numLines,array("class"=>"rowIndex"))
                      .'</td>';
            }else{
                echo '<td style="width: 77px;padding-top: 20px;">
                            <span style="float: left">'.$this->getButtonUpdateLine(true).'</span>       
                            <div class="remove" id ="'.$this->_idRemove.'_'.$this->_numLines.'"></div>
                            <div style="float: left; margin-left: 5px;">'.$this->getButtonDeleteLine(true).'</div>'
                            .CHtml::hiddenField($this->getId()."_rowIndex_$this->_numLines",$this->_numLines,array("class"=>"rowIndex"))
                     .'</td>';
            }
        }
        
        /**
         * Metodo para crear elemento ya sea del template 
         * de la tabla para el editInline true o para los elemetsPreCopy
         * 
         * @access private
         * @param string $element
         * @param array $options
         * @param string $render que funcion la solicita default 'elementTemplate'
         * @return string $field
         */
        private function createElement($element,$options, $render = 'elementTemplate'){
            $continue = false;
            $response ='';
            foreach($options as $key=>$option){
                if(is_int($key)&& is_array($option) && isset($option['name']))
                        $response.= $this->createElement($option['name'], $option, $render);
                else{
                    $continue = true;
                    break;
                }
                    
            }
            if($continue){
                $field = '';
                $error = '';

                $JLinesModel = new JLinesModel;                
                if(!isset($options['isModel']))
                       $options['isModel'] = true;

                if(!isset($options['htmlOptions']))
                       $options['htmlOptions'] = array();

                if(!isset($options['items']))
                       $options['items'] = array();
                
                if(isset($options['data']))
                       $options['items'] = $options['data'];
                
                if(!isset($options['type']))
                        $options['type'] = 'textField';
                 //Creamos los campos
                if($options['isModel'] && !isset($options['class']) && $field == ''){
                    $options['htmlOptions']['placeholder'] = $render == 'elementTemplate' ? '' : $this->model->getAttributeLabel($element);
                    //Campo para Mostrar error de validacion
                    if($this->_numLines === '{0}')
                        $error = $render == 'elementTemplate' ? '<div id="'.get_class($this->model).'_'.$this->_numLines.'_'.$element.'_em" class="help-inline error" style="display:none"></div>' : $this->form->error($JLinesModel,$element);
                    else
                        $error = $this->form->error($this->model,"[$this->_numLines]".$element);
                    //Validamos el tipo y mostramos
                    if(in_array($options['type'],self::$VALIDTYPES)){
                        if($options['type'] === 'checkBox')
                            $field = $render == 'elementTemplate' ? CHtml::label($this->form->$options['type']($this->model,"[$this->_numLines]$element",$options['htmlOptions']).$this->model->getAttributeLabel($element), get_class($this->model)."_{$this->_numLines}_$element", array('class'=>'checkbox')) : CHtml::label($this->form->$options['type']($JLinesModel,$element,$options['htmlOptions']).$this->model->getAttributeLabel($element), get_class($JLinesModel)."_$element", array('class'=>'checkbox'));
                        else
                           $field = $render == 'elementTemplate' ? $this->form->$options['type']($this->model,"[$this->_numLines]$element",$options['htmlOptions']) : $this->form->$options['type']($JLinesModel,$element,$options['htmlOptions']);
                    }
                    //Validamos los tipo items
                    if(in_array($options['type'],self::$VALIDTYPESITEMS)){
                        if(!isset($options['htmlOptions']['empty']))
                           $options['htmlOptions']['empty'] = $render == 'elementTemplate' ? Yii::t('app','Seleccione') : '--'.$this->model->getAttributeLabel($element).'--';
                        else
                            $options['htmlOptions']['empty'] = $render == 'elementTemplate' ? $options['htmlOptions']['empty'] : '--'.$this->model->getAttributeLabel($element).'--';
                        $field = $render == 'elementTemplate' ?  $this->form->$options['type']($this->model,"[$this->_numLines]$element",$options['items'],$options['htmlOptions']) : $this->form->$options['type']($JLinesModel,$element,$options['items'],$options['htmlOptions']);
                    }
                }elseif(in_array($options['type'],self::$VALIDTYPES) && !isset($options['name'])){
                    //si no es del modelo para que lo haga con el helper CHtml
                    if($options['type'] === 'checkBox')
                        $field = $render == 'elementTemplate' ? CHtml::label(CHtml::$options['type'](get_class($this->model)."[$this->_numLines][$element]",'',$options['htmlOptions']).$this->model->getAttributeLabel($element), get_class($this->model)."_{$this->_numLines}_$element", array('class'=>'checkbox')) : CHtml::label(CHtml::$options['type']($element,'',$options['htmlOptions']).$this->model->getAttributeLabel($element), $element, array('class'=>'checkbox'));
                    else
                       $field = $render == 'elementTemplate' ? CHtml::$options['type'](get_class($this->model)."[$this->_numLines][$element]",'',$options['htmlOptions']) : CHtml::$options['type']($element,'',$options['htmlOptions']);
                    
                }elseif(in_array($options['type'],self::$VALIDTYPES))
                    if($options['type'] === 'checkBox')
                        $field = $render == 'elementTemplate' ? CHtml::label(CHtml::$options['type'](get_class($this->model)."[$this->_numLines][".$options['name']."]",'',$options['htmlOptions']).$this->model->getAttributeLabel($options['name']), get_class($this->model)."_{$this->_numLines}_{$options['name']}", array('class'=>'checkbox')) : CHtml::label(CHtml::$options['type']($options['name'],'',$options['htmlOptions']).$this->model->getAttributeLabel($options['name']), $options['name'], array('class'=>'checkbox'));
                    else
                       $field = $render == 'elementTemplate' ? CHtml::$options['type'](get_class($this->model)."[$this->_numLines][".$options['name']."]",'',$options['htmlOptions']) : CHtml::$options['type']($options['name'],'',$options['htmlOptions']);

                    
                elseif(in_array($options['type'],self::$VALIDTYPESITEMS)){
                    if(!isset($options['htmlOptions']['empty']))
                       $options['htmlOptions']['empty'] = $render == 'elementTemplate' ? Yii::t('app','Seleccione') : '--'.$this->model->getAttributeLabel($element).'--';
                    else
                        $options['htmlOptions']['empty'] = $render == 'elementTemplate' ? $options['htmlOptions']['empty'] : '--'.$this->model->getAttributeLabel($element).'--';
                    $field = $render == 'elementTemplate' ?  CHtml::$options['type'](get_class($this->model)."[$this->_numLines][$element]",'',$options['items'],$options['htmlOptions']) : CHtml::$options['type'](get_class($JLinesModel)."[$element]",'',$options['items'],$options['htmlOptions']);
                }
                        
                $response .= '<div class="control-group">'
                            .$field
                            .$error
                        .'</div>';
            }
            return $response;
        }
}