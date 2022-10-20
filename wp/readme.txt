survey-kudir.php - используется как бэк для быстрого создания опросов

components-controller.php - используется для переиспользования кусков
одинакового кода в разныш баллонах

в папке faq компонт который использует контроллер выше.
в коде вызывается подобным образом
<?= Component::render('consult-form', [
    'radioTitle' => 'Какой то заголовок',
    'radioButtons' => ''',
    'anchorId' => 'consult-form',
]); ?>