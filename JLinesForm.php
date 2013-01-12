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
         * Constante para el sufijo de los objetos del formulario
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
        public $htmlAddOptions = array('id'=>'agregar','style'=>'margin-top: 5px;');
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
         * Inicializar Widget
         */
        public function init() {
            
            $this->render('lines');
            
            parent::init();
        }

        /**
         * Metodo para retornar un Boton de Nuevo en Lineas
         * @return TbButton or input 
         */
       public  function getButtonAddLine(){
                if(!isset($this->htmlAddOptions['id']))
                        $this->htmlAddOptions['id'] = 'agregar';
                
                return $this->widget('bootstrap.widgets.TbButton', array(
                                      'buttonType'=>'button',
                                      'size'=>'small',
                                      'type'=>'success',
                                      'icon'=>'plus white',
                                      'htmlOptions'=>$this->htmlAddOptions,
                            ),true);
                
                
        }
        /**
         * Metodo para retornar un Boton de Actualizar en Lineas
         * @return TbButton or input
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
         * @return TbButton or input
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
        public function getElementsPreCopy($form){
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
                //Creamos los campos del formulario
                if($options['isModel'] && isset($options['type'])){
                    //validamos el tipo y mostramos
                    if(in_array($options['type'],$this->getValidTypes())){
                        $type = $options['type'].self::SUFIJOBOOTSTRAP;
                        $campo = $form->$type($this->model,$element,$options['htmlOptions']);
                    }
                    //validamos los tipo items
                    if(in_array($options['type'],$this->getValidTypesItems())){
                        $type = $options['type'].self::SUFIJOBOOTSTRAP;
                        $campo = $form->$type($this->model,$element,$options['items'],$options['htmlOptions']);
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

?>
