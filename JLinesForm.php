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
        private static $VALIDTYPES = array('hiddenField','textField','fileField',/*'textArea',*/'uneditable','checkBox','radioButton');
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
            
            //inicializamos htmlOptions de botones
            $this->htmlDeleteOptions = array('id'=>$this->_idDelete.'_'.$this->_numLines,'class'=>'delete_'.$this->_idDelete,'name'=>$this->_numLines);
            $this->htmlUpdateOptions = array('id'=>$this->_idEdit.'_'.$this->_numLines,'class'=>'update_'.$this->_idEdit, 'name'=>$this->_numLines);
            
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
                //JlinesForm para model '.get_class($this->model).'
                $(function(){
                    $("#'.$this->_idNew.'").click(function(){
                        $("#'.$this->_idAdd.'").click();
                    });
                    $(".delete_'.$this->_idDelete.'").live("click",function(){
                        $("#'.$this->_idRemove.'_"+$(this).attr("name")).click();
                    });
                    $(".deleteLine_'.$this->getId().'").live("click",function(){
                        var idRow = $(this).attr("name");
                        var model = "'.get_class($this->model).'";
                        var deleteHidden = $("#'.get_class($this->model).'_delete").val()
                        var idFieldDelete = $("#'.get_class($this->model).'_"+idRow+"_'.$this->model->tableSchema->primaryKey.'").val();

                        deleteHidden = deleteHidden + idFieldDelete +",";
                        $("#"+model+"_delete").val(deleteHidden);
                            
                        //Reorganizamos los ids y names
                        var place = $(this).parents(".templateFrame:first").children(".templateTarget");
                        var countMax = place.find(".rowIndex").max();
                        var countFor = parseInt(idRow, 10)+1;
                        var line = parseInt(idRow, 10); 
                        for(var i = countFor ; i <=countMax; i++){
                            var fields = '.$this->getFields().';
                            var elements = '.$this->getElements().';
                            for(var x =0 ; x<=elements.length;x++){
                                if(elements[x] == "'.$this->getId().'_rowIndex")
                                    $("#"+elements[x]+"_"+i).val(line);
                                $("#"+elements[x]+"_"+i).attr({
                                    id: elements[x]+"_"+line,
                                    name: line
                                });
                            }
                                
                            for(var y =0 ; y<=fields.length;y++){
                                $("#"+model+"_"+i+"_"+fields[y]).attr({
                                   id: model+"_"+line+"_"+fields[y],
                                   name: model+"["+line+"]["+fields[y]+"]"
                               });
                           }
                           line++;
                        }
                    });
                });                
               '
           ,CClientScript::POS_HEAD);
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
             //array indexado que se escribira en CJSON::encode()
             $json = array();
             // array que tendra los modelos a ser validados
             $models=array();
             //Armamos el array de models para validar
             if(is_array($modelLines)){
                 foreach($modelLines as $data){
                     if(isset($_POST[get_class($data)])){
                             $models[]=$data;
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
             if(isset($_POST['jlines']) && $_POST['jlines']===$idForm){
                 
                 echo CActiveForm::validateTabular($models);
                 Yii::app()->end();
             }
            
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
                                if($model->findByPk($_POST[$nameModel][$count][$model->tableSchema->primaryKey]))
                                    $model = $model->findByPk($_POST[$nameModel][$count][$model->tableSchema->primaryKey]);
                                else
                                    $model = new $nameModel;
                            }
                            $model->attributes=$data;
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
                    echo '<tr class="templateContent">';
                        if(!array_key_exists($this->model->tableSchema->primaryKey, $this->elementsCopy))
                               echo $this->form->hiddenField($model,"[$i]".$this->model->tableSchema->primaryKey);
                        $this->_numLines = $i;
                        foreach($model as $name=>$value){
                            $this->model->$name = $value;
                            if(array_key_exists($name, $this->elementsCopy)){
                                echo '<td style="width: auto">'.$this->createElement($name, $this->elementsCopy[$name]).'</td>';
                            }
                        }
                        $this->htmlDeleteOptions['class']='delete_'.$this->_idDelete.' deleteLine_'.$this->getId();
                        $this->htmlDeleteOptions['id']=$this->_idDelete.'_'.$this->_numLines;
                        $this->htmlDeleteOptions['name']=$this->_numLines;
                        $this->renderAccionButtons();
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
                
                $this->renderAccionButtons();
                
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
                if($this->_numLines === '{0}')
                    $error = $render == 'elementTemplate' ? '<div id="'.get_class($this->model).'_'.$this->_numLines.'_'.$element.'_em" class="help-inline error" style="display:none"></div>' : $this->form->error($JLinesModel,$element);
                else
                    $error = $this->form->error($this->model,"[$this->_numLines]".$element);
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
