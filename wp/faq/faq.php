<?php
$title = 'Часто задаваемые вопросы';
$faq = [];

extract($params);
?>

<div class="finance evotor-pay__faq faq-component">
    <section class="faq-section">
        <div class="faq-section__inner">
            <div class="container container-fit-box">
                <h3 class="title title--size-m title--margin-m"><?= $title ?></h3>
                <div class="faq-section__list">
                    <?php foreach ($faq as $item) { ?>
                        <div class="faq-section__item">
                            <div class="faq-section__item-title"><?= $item['question']; ?></div>
                            <div class="faq-section__item-answertext">
                                <?= apply_filters('the_content', $item['answer']) ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </section>
</div>