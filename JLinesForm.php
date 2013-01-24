<?php
/**
 * Widget para Generar Lineas para un formulario de table detalle
 * Necesitas tener Instalado el bootstrap ya que se usan varios 
 * widgets de esta extencion
 * 
 *
 * Usado de Yii-Playground - Dynamics Row pero mejorado el uso
 * @link http://www.eha.ee/labs/yiiplay/index.php/en/site/extension?view=dynamicrows
 * @example
   $this->widget('ext.JLinesForm.JLinesForm',array(
            'model'=>$model,
            'htmlAddOptions'=>array('id'=>'agregar','disabled'=>true,'style'=>'margin-top: 5px;','tabindex'=>'10')
    ));
 * @author Juan David Rodriguez <juan.rodriguez@tramasoft.com>
 * @copyright 2013 - Juan David Rodriguez
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
         * Numero en que iniciaran el templeate de lineas
         * @var int $numLines
         */
        public $numLines = 0;
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
        public $_idAdd;
        /**
         * Modelo para las Lineas
         *
         * @var CModel $model
         */
        public $model;
        /**
         * Contenedor de el widget del formulario
         *
         * @var CActiveForm $form
         */
        public $form;
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
         * @access private
         * @var array  $htmlUpdateOptions
         * @access protected
         */
        private $htmlUpdateOptions = array('class'=>'edit','name'=>'{0}','id'=>'edit_{0}');
        /**
         * Opciones HTML para el boton de Eliminar linea
         * @access private
         * @var array  $htmlDeleteOptions
         * @access protected
         */
        private $htmlDeleteOptions = array('id'=>'eliminaLinea_{0}','class'=>'eliminaLinea','name'=>'{0}');
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
            if(!isset($this->form))
                    throw new Exception('Se debe definir la propiedad "form" en la llamada al widget');
            
            if(isset($this->htmlAddOptions['id']))
                    $this->_idAdd = $this->htmlAddOptions['id'];
            else
                    throw new Exception('Se debe definir un ID para el boton en la llamada al widget');
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
            $js->registerScriptFile($this->_assets .'/js/template.js');
            
            $js->registerScript($this->htmlAddOptions['id'],' 
                var send = true;
                $(document).ready(function(){
                    $(".clonar").click(function(){
                        $("#'.$this->htmlAddOptions['id'].'").click();
                    });
                    $(".eliminaLinea").live("click",function(){
                        $("#remover_"+$(this).attr("name")).click();
                    });
                });                
               '.$this->scriptAfterValidate()
           ,CClientScript::POS_HEAD);
        }
        /**
         * Metodo para retornar el script a usar
         * para la validacion de las lineas, 
         * para que funcione correctamente
         * se debe declarar en el formulario
         * 'clientOptions'=>array(
                'validateOnSubmit'=>true,
                'afterValidate'=>new CJavaScriptExpression('afterValidate'),
           ),
         * 
         * @return string  
         */
        private function scriptAfterValidate(){
                return '
                    function in_array(name,data){
                        var a = false;
                        $.each(data, function(id, errors){
                            if(name === id){
                                a = true
                                return false;
                            }
                        });
                        return a;
                    }
                    function afterValidate(form, json, hasError){
                        var attributes = '.CJavaScript::encode($this->model->attributes).';
                        $.ajax({
                            type:"POST",
                            url:'.CJavaScript::encode($this->getController()->createUrl('')).',
                            data: $("#'.$this->form->id.'").serialize()+"&jlines='.$this->form->id.'",
                            dataType : "json",
                            success: function(data){
                                if(data != ""){
                                    $.each(data, function(id, errors){
                                        $("#"+id).parents(".row").addClass("error");
                                        $.each(errors,function(index,error){
                                            $("#"+id+"_em").text(error);
                                            return false;
                                        });
                                        $("#"+id+"_em").show();
                                        $.each(attributes,function(name,value){
                                            var attribute = id.split("_")[0]+"_"+id.split("_")[1]+"_"+name
                                            if(!in_array(attribute,data)){
                                                $("#"+attribute).parents(".row").addClass("success");
                                                $("#"+attribute+"_em").hide();
                                                $("#"+attribute+"_em").text("");
                                            }
                                        });
                                    });
                                    send = false;
                                }else{
                                    send = true;
                                    $(".row").addClass("success");
                                }
                                 
                            },
                       });
                       return send;
                    }
                    
                ';
        }
        /*
         * Metodo para retornar el contador
         * que debe ir en el count de el footer de la tabla 
         */
        public function getCountColspan(){
            return count($this->elementsCopy)+1;
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
         * @param string $string si es un string o no
         * @return TbButton 
         */
        public  function getButtonUpdateLine($string = false){
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
        public  function getButtonDeleteLine($string = false){
                return $this->widget('bootstrap.widgets.TbButton', array(
                                  'buttonType'=>'button',
                                  'size'=>'small',
                                  'type'=>'danger',
                                  'icon'=>'minus white',
                                  'htmlOptions'=>$this->htmlDeleteOptions,
                            ),$string);
                
            
        }
        /**
         * Este metodo retornara la validacion a usar
         * para las lineas, si las validateTabular, o la
         * validate en caso de modal y elementsPreCopy
         * @param CModel $model 
         * @param string $idForm 
         * @static
         */
        public static function validate($model,$idForm){
             //array indexado que se escribira en CJSON::encode()
             $json = array();
             // array que tendra los modelos a ser validados
             $models=array();
             
             if(isset($_POST[get_class($model)])){
                   foreach($_POST[get_class($model)] as $i=>$data){
                           $models[$i]=$model;
                           $models[$i]['PRECIO_UNITARIO']=0;
                           $models[$i]['PORC_DESCUENTO']=0;
                           $models[$i]['MONTO_DESCUENTO']=0;
                           $models[$i]['PORC_IMPUESTO']=0;
                           $models[$i]['VALOR_IMPUESTO']=0;
                           $models[$i]['TIPO_PRECIO']=130;
                           
                   }
             }
             //verifica que haya una peticion ajax
             if(isset($_POST['ajax']) && $_POST['ajax']===$idForm){
                 //Escribimos lo errores de validacion en nuestro array $json
                   if(isset($_POST[get_class($model)])){
                         foreach(CJSON::decode(CActiveForm::validateTabular($models)) as $index=>$value)
                                 $json[$index]=$value;
                   }
                   if(isset($_POST['JLinesModel'])){
                       // Instanciamos el Model Clonado y asignamos propiedades
                         $JLinesModel = new JLinesModel;
                         $JLinesModel->rules = $model->rules();
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
                 if(isset($_POST[get_class($model)])){
                     foreach(CJSON::decode(CActiveForm::validateTabular($models)) as $index=>$value)
                                 $json[$index]=$value;
                 }
                 echo CJSON::encode($json);
                 Yii::app()->end();
             }
            
        }
              
        /**
         * Metodo para retornar los elementos que se van a copiar
         */
        public function renderElementsPreCopy(){
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
        public function renderHeaders(){
            if(is_array($this->elementsCopy)){
                echo '<thead>';
                    foreach($this->elementsCopy as $attribute=>$options){
                        if(isset($options['label']))
                            echo '<th>'.$options['label'].'</th>';
                        elseif(!isset($options['isModel']) ||  $options['isModel']==true)
                            echo '<th>'.$this->model->getAttributeLabel($attribute).'</th>';
                    }
                    echo '<th></th>';
                echo '</thead>';
            }
        }
        public function renderElementsSaved(){
            
        }
        /**
         * Metodo para mostrar el contenido del template
         * que se va a tener en cuenta al momento de la clonacion 
         * del botoon (.add)
         * 
         */
        public function renderElementsTemplate(){
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
                                      <div class="remove" id ="remover_{0}"></div>
                                      <div style="float: left; margin-left: 5px;">'.$this->getButtonDeleteLine(true).'</div>'
                                      .CHtml::hiddenField("rowIndex_{0}","{0}",array("class"=>"rowIndex"))
                             .'</td>';
            }else{
                echo '<td style="width: 77px;padding-top: 20px;">
                            <span style="float: left">'.$this->getButtonUpdateLine(true).'</span>       
                            <div class="remove" id ="remover_{0}"></div>
                            <div style="float: left; margin-left: 5px;">'.$this->getButtonDeleteLine(true).'</div>'
                            .CHtml::hiddenField("rowIndex_{0}","{0}",array("class"=>"rowIndex"))
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
         * @param string $render que funcion la solicita default 'elementCopy'
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
                $error = $render == 'elementTemplate' ? '<div id="'.get_class($this->model).'_{'.$this->numLines.'}_'.$element.'_em" class="errorMessage" style="display:none"></div>' : $this->form->error($JLinesModel,$element);
                //Validamos el tipo y mostramos
                if(in_array($options['type'],self::$VALIDTYPES)){
                       $campo = $render == 'elementTemplate' ? $this->form->$options['type']($this->model,'[{'.$this->numLines.'}]'.$element,$options['htmlOptions']) : $this->form->$options['type']($JLinesModel,$element,$options['htmlOptions']);
                }
                //Validamos los tipo items
                if(in_array($options['type'],self::$VALIDTYPESITEMS)){
                       $options['htmlOptions']['empty'] = $render == 'elementTemplate' ? 'Seleccione' : '--'.$this->model->getAttributeLabel($element).'--';
                       $campo = $render == 'elementTemplate' ?  $this->form->$options['type']($this->model,'[{'.$this->numLines.'}]'.$element,$options['items'],$options['htmlOptions']) : $this->form->$options['type']($JLinesModel,$element,$options['items'],$options['htmlOptions']);
                }
            }elseif(isset($options['type']) && in_array($options['type'],self::$VALIDTYPES)){
                //si no es del modelo para que lo haga con el helper CHtml
                $campo = $render == 'elementTemplate' ? CHtml::$options['type'](get_class($this->model).'[{'.$this->numLines.'}]'."[$element]",'',$options['htmlOptions']) : CHtml::$options['type']($element,'',$options['htmlOptions']);
            }
            
            return '<div class="row">'.$campo.$error.'</div>';
        }
}

?>
