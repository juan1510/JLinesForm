<?php
/**
 * Widget para Generar Lineas para un formulario de table detalle o para entradas Tabulares
 * Necesitas tener Instalado el bootstrap ya que se usan varios 
 * widgets de esta extension
 * 
 *
 * Usado de Yii-Playground - Dynamics Row pero mejorado el uso
 * @link http://www.eha.ee/labs/yiiplay/index.php/en/site/extension?view=dynamicrows
 * @example
   $this->widget('ext.JLinesForm.JLinesForm',array(
            'model'=>$model,
            'htmlAddOptions'=>array('id'=>'agregar','disabled'=>true,'style'=>'margin-top: 5px;','tabindex'=>'10')
    ));
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
        private static $VALIDTYPES = array('hiddenField','textField','fileField','textArea','uneditable','checkBox','radioButton');
        /**
         * Tipos validos para los $elementsPreCopy con items
         * @var array
         * @access private
         * @static
         */
        private static $VALIDTYPESITEMS = array('dropDownList','checkBoxListInline','checkBoxList','radioButtonList');
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
         * @var CModel $model
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
         * Inicializar Widget
         */
        public function init() {
            if(!isset($this->form))
                    throw new Exception('Se debe definir la propiedad "form" en la llamada al widget');
            if(!isset($this->model))
                    throw new Exception('Se debe definir la propiedad "model" en la llamada al widget');
            //Asignamos lod ids correspondientes
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
            
            $this->registerScripts();
            
            $this->render($this->editInline ? 'lines_edit':'lines');
            
            parent::init();
        }
        /**
         * Este metodo registrara los js 
         * Nesesarios a usar y algun codigo extra
         * que se necesite con respecto a los botones
         * @access private
         */
        private function registerScripts(){
            
            $js = Yii::app()->clientScript;
            
            $this->_assets = Yii::app()->assetManager->publish(dirname(__FILE__).DIRECTORY_SEPARATOR.'assets');
            $js->registerCoreScript('jquery');
            $js->registerScriptFile($this->_assets .'/js/jquery.calculation.min.js');
            $js->registerScriptFile($this->_assets .'/js/jquery.format.js');
            $js->registerScriptFile($this->_assets .'/js/JLinesForm.js');
            
            $js->registerScript($this->_idAdd,' 
                $(function(){
                    $("#'.$this->_idNew.'").click(function(){
                        $("#'.$this->_idAdd.'").click();
                    });
                    $(".delete_'.$this->_idDelete.'").live("click",function(){
                        $("#'.$this->_idRemove.'_"+$(this).attr("name")).click();
                    });
                });                
               '
           ,CClientScript::POS_HEAD);
        }
        /**
         * Este metodo se encarga de validacion de las lineas,
         * tanto para el metodo editInlite true o false
         * @param mixes $modelLineas Puede ser CModel con objeto de un solo modelo o un array con varios (para cuando usa varias veces el widget)
         * @param string $idForm 
         * @static
         */
        public static function validate($modelLineas,$idForm){
             //array indexado que se escribira en CJSON::encode()
             $json = array();
             // array que tendra los modelos a ser validados
             $models=array();
             //Armamos el array de models para validar
             if(is_array($modelLineas)){
                 foreach($modelLineas as $i=>$data){
                     if(isset($_POST[get_class($data)])){
                            foreach($_POST[get_class($data)] as $j=>$model)
                                    $models[$j]=$data;
                     }
                 }
             }elseif(isset($_POST[get_class($modelLineas)])){
                   foreach($_POST[get_class($modelLineas)] as $i=>$data)
                           $models[$i]=$modelLineas;
             }
             //verifica que haya una peticion ajax
             if(isset($_POST['ajax']) && $_POST['ajax']===$idForm){
                   if(isset($_POST['JLinesModel'])){
                       // Instanciamos el Model Clonado y asignamos propiedades
                         $JLinesModel = new JLinesModel;
                         $JLinesModel->rules = $modelLineas->rules();
                       //asignamos lo valores del post al modelo
                       foreach($_POST['JLinesModel'] as $name=>$value)
                            $JLinesModel->$name = $value;
                       //Escribimos lo errores de validacion en nuestro array $json
                       foreach(CJSON::decode(CActiveForm::validate($JLinesModel)) as $index=>$value)
                             $json[$index]=$value;
                   }
                   //Finalmente mostramos los errores de validacion
                     echo CJSON::encode($json);
                     Yii::app()->end();
             }
             if(isset($_POST['jlines']) && $_POST['jlines']===$idForm){
                 //Escribimos lo errores de validacion en nuestro array $json
                 foreach(CJSON::decode(CActiveForm::validateTabular($models)) as $index=>$value)
                     $json[$index]=$value;
                 
                 echo CJSON::encode($json);
                 Yii::app()->end();
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
            $this->htmlUpdateOptions = array('id'=>$this->_idEdit.'_'.$this->_numLines,'class'=>'update_'.$this->_idEdit, 'name'=>$this->_numLines);
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
            $this->htmlDeleteOptions = array('id'=>$this->_idDelete.'_'.$this->_numLines,'class'=>'delete_'.$this->_idDelete,'name'=>$this->_numLines);
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
                            echo '<th><strong>'.$options['label'].'</strong></th>';
                        elseif(!isset($options['isModel']) ||  $options['isModel']==true)
                            echo '<th><strong>'.$this->form->label($this->model,$attribute).'</strong></th>';
                    }
                    echo '<th></th>';
                echo '</thead>';
            }
        }
        protected function renderElementsSaved(){
            
        }
        /**
         * Metodo para mostrar el contenido del template
         * que se va a tener en cuenta al momento de la clonacion 
         * a causa del botoon (.add)
         * 
         */
        protected function renderElementsTemplate(){
            if(is_array($this->elementsCopy)){
                $countElements = count($this->elementsCopy) -1;
                $cont =0;
                
                //Recorremos los elementos y asignamos valores en caso de vacios
                foreach($this->elementsCopy as $element=>$options){
                     echo '<td style="width: auto">'.$this->createElement($element, $options).'</td>';
                     if($cont == $countElements)
                         $this->renderAccionButtons();
                     $cont++;
               }
                
            }
        }
        /**
         * Metodo para mostrar los botones a usar
         * en las acciones de la linea
         * @access private
         */
        private function renderAccionButtons(){
            if($this->editInline){
                echo '<td style="width: 47px;padding-top: 20px;"> 
                                      <div class="remove" id ="'.$this->_idRemove.'_'.$this->_numLines.'"></div>
                                      <div style="float: left; margin-left: 5px;">'.$this->getButtonDeleteLine(true).'</div>'
                                      .CHtml::hiddenField("rowIndex_$this->_numLines",$this->_numLines,array("class"=>"rowIndex"))
                      .'</td>';
            }else{
                echo '<td style="width: 77px;padding-top: 20px;">
                            <span style="float: left">'.$this->getButtonUpdateLine(true).'</span>       
                            <div class="remove" id ="'.$this->_idRemove.'_'.$this->_numLines.'"></div>
                            <div style="float: left; margin-left: 5px;">'.$this->getButtonDeleteLine(true).'</div>'
                            .CHtml::hiddenField("rowIndex_$this->_numLines",$this->_numLines,array("class"=>"rowIndex"))
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
         * @return string $campo
         */
        private function createElement($element,$options, $render = 'elementTemplate'){
            $campo = '';
            $error = '';
            $JLinesModel = $render != 'elementTemplate' ? new JLinesModel : $this->model;
            if(!isset($options['isModel']))
                   $options['isModel'] = true;

            if(!isset($options['htmlOptions']))
                   $options['htmlOptions'] = array();

            if(!isset($options['items']))
                   $options['items'] = array();
             //Creamos los campos
            if($options['isModel'] && isset($options['type'])){
                $options['htmlOptions']['placeholder'] = $render == 'elementTemplate' ? '' : $this->model->getAttributeLabel($element);
                //Campo para Mostrar error de validacion
                $error = $render == 'elementTemplate' ? '<div id="'.get_class($this->model).'_'.$this->_numLines.'_'.$element.'_em" class="help-inline error" style="display:none"></div>' : $this->form->error($JLinesModel,$element);
                //Validamos el tipo y mostramos
                if(in_array($options['type'],self::$VALIDTYPES)){
                       $campo = $render == 'elementTemplate' ? $this->form->$options['type']($this->model,"[$this->_numLines]".$element,$options['htmlOptions']) : $this->form->$options['type']($JLinesModel,$element,$options['htmlOptions']);
                }
                //Validamos los tipo items
                if(in_array($options['type'],self::$VALIDTYPESITEMS)){
                       $options['htmlOptions']['empty'] = $render == 'elementTemplate' ? 'Seleccione' : '--'.$this->model->getAttributeLabel($element).'--';
                       $campo = $render == 'elementTemplate' ?  $this->form->$options['type']($this->model,"[$this->_numLines]".$element,$options['items'],$options['htmlOptions']) : $this->form->$options['type']($JLinesModel,$element,$options['items'],$options['htmlOptions']);
                }
            }elseif(isset($options['type']) && in_array($options['type'],self::$VALIDTYPES)){
                //si no es del modelo para que lo haga con el helper CHtml
                $campo = $render == 'elementTemplate' ? CHtml::$options['type'](get_class($this->model)."[$this->_numLines]"."[$element]",'',$options['htmlOptions']) : CHtml::$options['type']($element,'',$options['htmlOptions']);
            }
            
            return '<div class="control-group">'
                        .$campo
                        .$error
                    .'</div>';
        }
}

?>
