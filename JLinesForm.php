<?php
/**
 * Widget para Generar Lineas para un formulario de table detalle
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
         * Modelo para las Lineas
         *
         * @var CModel $model
         */
        public $model;
        /**
         * Si usas Bootstrap en tu aplicacion se usaran
         * los botones de esta extension default true
         * 
         * @var boolean  $useBootstrap
         */
        public $useBootstrap = true;
        /**
         * Opciones HTML para el boton de agregar linea
         * ya que aveces se necesita que este boton tenga 
         * ciertos atributos html para manejar mediante eventos jQuery
         * 
         * @var array  $htmlAddOptions
         */
        public $htmlAddOptions = array('id'=>'agregar','style'=>'margin-top: 5px;');

        public function init() {
            $this->render('lines');
            parent::init();
        }

        /**
         * Metodo para retornar un Boton de Nuevo en Lineas
         * @return TbButton or input 
         */
       public  function getButtonAddLine(){
                if($this->useBootstrap){
                    return $this->widget('bootstrap.widgets.TbButton', array(
                                      'buttonType'=>'button',
                                      'label'=>$label,
                                      'size'=>'small',
                                      'type'=>'success',
                                      'icon'=>'plus white',
                                      'htmlOptions'=>$this->htmlAddOptions,
                            ));
                }else{
                    CHtml::button('+',$htmlOptions);
                }
                
        }
        /**
         * Metodo para retornar un Boton de Actualizar en Lineas
         * @param mixed $htmlOptions Opciones HTML del Boton default 'array()'
         * @return TbButton or input
         */
        public  function getButtonUpdateLine($htmlOptions = array()){
                if($this->useBootstrap){
                   return $this->widget('bootstrap.widgets.TbButton', array(
                                  'buttonType'=>'button',
                                  'size'=>'small',
                                  'icon'=>'pencil',
                                  'htmlOptions'=>array('class'=>'edit','name'=>'{0}','id'=>'edit_{0}'),
                            ));
                }else{
                    CHtml::button('editar',array('class'=>'edit','name'=>'{0}','id'=>'edit_{0}'));
                }
            
        }
        /**
         * Metodo para retornar un Boton de Eliminar en Lineas
         * @param string $label Label del Boton default 'Nuevo'
         * @param mixed $htmlOptions Opciones HTML del Boton default 'array()'
         * @return TbButton or input
         */
        public  function getButtonDeleteLine($label = 'Eliminar',$htmlOptions = array()){
                if($this->useBootstrap){
                   return $this->widget('bootstrap.widgets.TbButton', array(
                                  'buttonType'=>'button',
                                  'label'=>$label,
                                  'size'=>'small',
                                  'type'=>'danger',
                                  'icon'=>'minus white',
                                  'htmlOptions'=>$htmlOptions,
                            ));
                }else{
                    CHtml::button('-',$htmlOptions);
                }
            
        }
}

?>
