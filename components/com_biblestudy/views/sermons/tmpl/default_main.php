<?php
/**
 * Default for sermons
 *
 * @package    BibleStudy.Site
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;
if (BIBLESTUDY_CHECKREL)
{
    JHtml::_('bootstrap.framework');
}

$JBSMListing = new JBSMListing();
?>

<div class="container-fluid">
    <div id="bsheader">
        <?php
        if ($this->params->get('showpodcastsubscribelist') == 1)
        {
            echo $this->subscribe;
        }
        ?>
    </div>
    <?php if ($this->params->get('intro_show') > 0)
    { ?>
    <div class="hero-unit" style="padding-top:30px; padding-bottom:20px;">
        <?php
        if ($this->params->get('listteachers') && $this->params->get('list_teacher_show') > 0)
        {
            ?>
            <div class="row-fluid" >
                <ul class="thumbnails">
                    <?php $spans = 12 / count($teachers);
                    foreach ($teachers as $teacher)
                    {
                        echo '<li class="span'.$spans.'">';
                        if ($this->params->get('teacherlink')> 0)
                        {echo '<a href="index.php?option=com_biblestudy&view=teacher&id='.$teacher['id'].'&t='.$teacher['t'].'" ><img class="img-polaroid" src="'.JURI::base().$teacher['image'].'"></a>';}
                        else {echo '<img class="img-polaroid" src="'.JURI::base().$teacher['image'].'">';}
                        if ($this->params->get('teacherlink')> 0)
                        {echo '<div class="caption"><p><a href="index.php?option=com_biblestudy&view=teacher&id='.$teacher['id'].'&t='.$teacher['t'].'">'.$teacher['name'].'</a></p>';}
                        else {echo '<div class="caption"><p>'.$teacher['name'].'</p></div>';}
                        echo '</li>';
                    }
                    ?>
                </ul>
            </div>
        <?php } ?>
        <div class="row-fluid">
            <div class="span12">
                <?php if ($this->params->get('show_page_image') > 0){?> <img class="imgcenter" src="<?php echo JURI::base() . $this->main->path; ?>"><?php }?>
                <?php if ($this->params->get('show_page_title') == 1){?><h2><?php echo $this->params->get('page_title');?></h2><?php }?>
                <?php if ($this->params->get('list_intro')){?><p><?php echo $this->params->get('list_intro');?></p><?php }?>
            </div>
        </div>
    </div>

</div><!-- .hero-unit -->
<?php }?>

<div class="container-fluid">

    <?php echo $this->page->dropdowns;?>
    <hr />
    <?php $listing = new JBSMListing;
    $list = $listing->getFluidListing($this->items, $this->params, $this->admin_params, $this->template, $type='sermons');
    echo $list;
    ?>
    <div class="listingfooter pagination">
        <?php
        if ($this->params->get('show_pagination') == 2)
        {
            echo '<span class="display-limit">' . JText::_('JGLOBAL_DISPLAY_NUM') . $this->pagination->getLimitBox() . '</span>';
        }
        echo $this->pagination->getPageslinks();
        ?>
    </div>
    <?php
    if ($this->params->get('showpodcastsubscribelist') == 2)
    {
        echo $this->subscribe;
    }
    ?>
</div>
