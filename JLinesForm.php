<?php
/**
 * Widget para Generar Lineas para un formulario de table detalle
 * Necesitas tener Instalado el bootstrap ya que se usan varios 
 * widgets de esta extencion
 * 
 *
 * Usado de Yii-Playground - Dynamics Row pero mejorado el uso
 * @link http://www.eha.ee/labs/yiiplay/index.php/en/site/extension?view=dynamicrows
 * 
 * $this->widget('ext.JLinesForm.JLinesForm',array(
            'model'=>$model,
            'htmlAddOptions'=>array('id'=>'agregar','disabled'=>true,'style'=>'margin-top: 5px;','tabindex'=>'10')
    ));
 * @author Juan David Rodriguez <juan.rodriguez@tramasoft.com>
 * @copyright 2013 - Juan David Rodriguez
 * @license New BSD License
 * @category User Interface
 * @version 0.1
 */
class JLinesForm extends CWidget{
        /**
         * Constante para el sufijo de los objetos del formulario Bootstrap
         */
        const SUFIJOBOOTSTRAP = 'Row';
        /**
         * Tipos validos para los $elementsPreCopy
         * @var array
         * @access private
         * @static
         */
        private static $VALIDTYPES = array('textField','fileField','textArea','uneditable','checkBox','radioButton');
        /**
         * Tipos validos para los $elementsPreCopy con items
         * @var array
         * @access private
         * @static
         */
        private static $VALIDTYPESITEMS = array('dropDownList','checkBoxListInline','checkBoxList','radioButtonList');
        /**
         * Url de la carpeta asstes dentro de extensions
         *
         * @var string $_assets
         */
        private $_assets ;
        /**
         * Modelo para las Lineas
         *
         * @var CModel $model
         */
        public $model;
        /**
         * Opciones HTML para el boton de agregar linea
         * ya que aveces se necesita que este boton tenga 
         * ciertos atributos html para manejar mediante eventos jQuery
         * 
         * @var array  $htmlAddOptions
         */
        public $htmlAddOptions = array('id'=>'add','style'=>'margin-top: 5px;');
        /**
         * Opciones HTML para el boton de Actualizar linea
         * 
         * @var array  $htmlUpdateOptions
         * @access protected
         */
        protected $htmlUpdateOptions = array('class'=>'edit','name'=>'{0}','id'=>'edit_{0}');
        /**
         * Opciones HTML para el boton de Eliminar linea
         * 
         * @var array  $htmlDeleteOptions
         * @access protected
         */
        protected $htmlDeleteOptions = array('id'=>'eliminaLinea_{0}','class'=>'eliminaLinea','name'=>'{0}');
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
         * Bandera para saber a que vista dirigir,
         * si a la vista "lines" opciones con dobleclick y modal
         * o a la vista "lines_edit" solo editando campos en la tabla
         * @var boolean $editInline
         */
        public $editInline = true;
        
        /**
         * Inicializar Widget
         */
        public function init() {
            if(!isset($this->htmlAddOptions['id']))
                      throw new Exception('Se debe definir un ID para el boton');
            $this->registerScripts();
            
            $this->render($this->editInline ? 'lines_edit':'lines');
            
            parent::init();
        }
        /**
         * Este metodo registrara los js 
         * Nesesarios a usar y algun codigo extra
         * que se necesite con respecto a los botones
         */
        public function registerScripts(){
            
            $js = Yii::app()->clientScript;
            
            $this->_assets = Yii::app()->assetManager->publish(dirname(__FILE__).DIRECTORY_SEPARATOR.'assets');
            $js->registerCoreScript('jquery');
            $js->registerScriptFile($this->_assets .'/js/jquery.format.js');
            $js->registerScriptFile($this->_assets .'/js/template.js');
            
            $js->registerScript($this->htmlAddOptions['id'],'
                $(document).ready(function(){
                    $(".clonar").click(funcion(){
                        $("#'.$this->htmlAddOptions['id'].'").click();
                    });
                });
            ');
        }
        /*
         * Metodo para retornar el contador
         * que debe ir en el count de el footer de la tabla 
         */
        public function getCountColspan(){
            return count($this->elementsCopy);
        }
        /**
         * Metodo para retornar un Boton de Nuevo en Lineas
         * @return TbButton 
         */
       public  function getButtonAddLine(){ 
                if(isset($this->htmlAddOptions['class']))
                    $this->htmlAddOptions['class'] += ' clonar';                
                else
                    $this->htmlAddOptions['class'] = 'clonar';
                if(isset($this->htmlAddOptions['id']))
                    unset($this->htmlAddOptions['id']);
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
         * @return TbButton 
         */
        public  function getButtonUpdateLine(){
                return $this->widget('bootstrap.widgets.TbButton', array(
                                  'buttonType'=>'button',
                                  'size'=>'small',
                                  'icon'=>'pencil',
                                  'htmlOptions'=>$this->htmlUpdateOptions,
                            ));
                
            
        }
        /**
         * Metodo para retornar un Boton de Eliminar en Lineas
         * @param mixed $htmlOptions Opciones HTML del Boton default 'array()'
         * @return TbButton
         */
        public  function getButtonDeleteLine(){
                return $this->widget('bootstrap.widgets.TbButton', array(
                                  'buttonType'=>'button',
                                  'size'=>'small',
                                  'type'=>'danger',
                                  'icon'=>'minus white',
                                  'htmlOptions'=>$this->htmlDeleteOptions,
                            ));
                
            
        }
        /**
         * Tipos de elementos a usar
         * @return array  
         */
        public static function getValidTypes(){
           return self::$VALIDTYPES;
        }
        /**
         * Tipos de elementos a usar con items
         * @return array  
         */
        public static function getValidTypesItems(){
           return self::$VALIDTYPESITEMS;
        }
        /**
         * Metodo para retornar los elementos que se van a copiar
         * @param TbActiveForm $form 
         */
        public function renderElementsPreCopy($form){
            if(is_array($this->elementsPreCopy)){
                $countElements = count($this->elementsPreCopy) -1;
                $cont =0;
                //Recorremos los elementos y asignamos valores en caso de vacios
                foreach($this->elementsPreCopy as $element=>$options){

                    if(!isset($options['isModel']))
                        $options['isModel'] = true;

                    if(!isset($options['htmlOptions']))
                        $options['htmlOptions'] = array();

                    if(!isset($options['items']))
                        $options['items'] = array();
                    //Creamos los campos del formulario-pre
                    if($options['isModel'] && isset($options['type'])){
                        //validamos el tipo y mostramos
                        if(in_array($options['type'],$this->getValidTypes())){
                            $type = $options['type'].self::SUFIJOBOOTSTRAP;
                            $campo = $form->$type($this->model,$element,$options['htmlOptions']);
                        }
                        //validamos los tipo items
                        if(in_array($options['type'],$this->getValidTypesItems())){
                            $options['htmlOptions']['empty'] = '--'.$this->model->getAttributeLabel($element).'--';
                            $campo = $form->$options['type']($this->model,$element,$options['items'],$options['htmlOptions']);
                        }
                    }elseif(isset($options['type'])){
                        //si no es del modelo para que lo haga con el helper CHtml
                        $campo = CHtml::$options['type']($element,'',$options['htmlOptions']);
                    }
                        echo '<td style="width: auto">'.$campo.'</td>';
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
        public function renderHeaders(){
            if(is_array($this->elementsCopy)){
                foreach($this->elementsCopy as $attribute=>$options){
                    if(isset($options['label']))
                        echo '<th>'.$options['label'].'</th>';
                    elseif(!isset($options['isModel']) ||  $options['isModel']==true)
                        echo '<th>'.$this->model->getAttributeLabel ($attribute).'</th>';
                }
                echo '<th></th>';
            }
        }
}

?>
