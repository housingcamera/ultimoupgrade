This is how to read the customer attribute

<?php if($_customAttribute = $this->getCurrentCategory()->getCustomAttribute()): ?>
    <?php echo $_helper->categoryAttribute($_category, $_customAttribute, 'custom_attribute') ?>
<?php endif; ?>