<?php
    /*
     * Lineas Renderizadas para modo edit segun opciones del Widget
     */
     /* @var $this JLinesForm */
?>
<table class="templateFrame table table-bordered table table-hover table table-striped" cellspacing="0">
       <?php $this->renderHeaders();?>
      <tbody class="templateTarget">
             <?php if($this->displayElementsSaved)$this->renderElementsSaved(); ?>
             <?php echo CHtml::hiddenField(get_class($this->model).'_delete','' ); ?>
      </tbody>
      <tfoot>
             <tr>
                 <td colspan='<?php echo $this->getCountColspan();?>'>
                        <div id='<?php echo $this->_idAdd;?>' class="add"></div>
                        <?php echo $this->getButtonAddLine();?>
                        <div id="widgets" style="display: none">
                            <?php echo $this->renderElementsWidgets();?>
                        </div>
                        <textarea class="template" style="display:none">
                                  <tr class="templateContent">
                                      <?php $this->renderElementsTemplate();?>
                                  </tr>
                        </textarea>
                 </td>
            </tr>
      </tfoot>
</table>
