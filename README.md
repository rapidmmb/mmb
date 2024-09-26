# Mmb On Laravel
<!-- TOC -->
* [Mmb On Laravel](#mmb-on-laravel)
  * [Installation](#installation)
    * [Publish Configs](#publish-configs)
  * [Actions](#actions)
    * [Section](#section)
    * [Form](#form)
      * [Settings](#settings)
      * [Attributes](#attributes)
      * [Requests](#requests)
    * [Form Has Chunk](#form-has-chunk)
      * [Inputs](#inputs)
      * [Requests](#requests-1)
    * [Input](#input)
      * [Request Message](#request-message)
      * [Keyboards](#keyboards)
      * [Filters](#filters)
      * [Advanced Filters](#advanced-filters)
      * [Events](#events)
      * [Settings](#settings-1)
    * [Form Key](#form-key)
      * [Replacement](#replacement)
      * [Action](#action)
      * [Action With Pass Value](#action-with-pass-value)
      * [Enabled Condition](#enabled-condition)
    * [Command](#command)
      * [Command Value](#command-value)
      * [Parameter](#parameter)
      * [Separate Method](#separate-method)
      * [Start Command](#start-command)
    * [Handle Callback](#handle-callback)
      * [Auto Match](#auto-match)
      * [Custom Match](#custom-match)
      * [Inline Key](#inline-key)
    * [Middle Action](#middle-action)
      * [Request](#request)
      * [Required](#required)
      * [Arguments](#arguments)
      * [Required Globally](#required-globally)
    * [Resource Section](#resource-section)
      * [Resource List](#resource-list)
      * [Resource Info](#resource-info)
      * [Resource Create/Edit](#resource-createedit)
      * [Resource Search](#resource-search)
      * [Resource Simple Filter](#resource-simple-filter)
      * [Resource Order](#resource-order)
      * [Resource Delete](#resource-delete)
  * [Inline Actions](#inline-actions)
    * [Menu](#menu)
      * [Advanced Key](#advanced-key)
      * [Regex Pattern](#regex-pattern)
      * [Default Message](#default-message)
    * [Inline Form](#inline-form)
      * [Scope](#scope)
    * [Dialog](#dialog)
      * [Key Id](#key-id)
      * [Reload](#reload)
      * [Data Model](#data-model)
      * [Example](#example)
    * [Fixed Dialog](#fixed-dialog)
      * [Variant](#variant)
      * [Within](#within)
      * [Argument](#argument)
    * [Base](#base)
      * [Events](#events-1)
      * [Variant history](#variant-history)
  * [Handlers](#handlers)
      * [Example](#example-1)
      * [Custom Handling](#custom-handling)
  * [Query Matcher](#query-matcher)
    * [Pattern](#pattern)
      * [Argument](#argument-1)
      * [Argument Type](#argument-type)
      * [Nullable Type](#nullable-type)
    * [Match](#match)
      * [Action](#action-1)
      * [Action From](#action-from)
      * [Json](#json)
      * [Options](#options)
    * [Special Value](#special-value)
      * [Regex Pattern](#regex-pattern-1)
      * [Same](#same)
      * [In](#in)
    * [Auto Match](#auto-match-1)
    * [Advanced](#advanced)
      * [Find Pattern](#find-pattern)
      * [Make Query](#make-query)
  * [Area](#area)
  * [Multiple Bot](#multiple-bot)
    * [Default Channeling](#default-channeling)
    * [Creative Channeling](#creative-channeling)
  * [Rule & Permissions](#rule--permissions)
    * [Install Package](#install-package)
    * [Setup Model](#setup-model)
    * [Setup Seeder](#setup-seeder)
    * [Setup Area](#setup-area)
    * [Introduce Area](#introduce-area)
  * [nn](#nn)
<!-- TOC -->


## Installation
TODO !

### Publish Configs
```shell
php artisan vendor:publish --tag=mmb
```


## Actions

### Section

```php
class HomeSection extends Section
{
    public function main()
    {
        $this->menu('mainMenu')->response("Welcome!");
    }

    public function mainMenu(Menu $menu)
    {
        $menu
            ->schema([
                [
                    $menu->key("Hello World!", 'helloWorld'),
                ],
            ])
        ;
    }
    
    public function helloWorld()
    {
        $this->response("Hi =D");
    }
}
```

### Form
```php
class TestForm extends Form
{

    protected $inputs = [
        'amount',
        'submit',
    ];

    public function amount(Input $input)
    {
        $input
            ->unsignedInt()
            ->ask("Enter amount:")
        ;
    }

    public function submit(Input $input)
    {
        $input
            ->onlyOptions()
            ->options([
                [$input->key("Submit", true)]
            ])
            ->ask("Submit?");
    }

    public function onFinish()
    {
        $this->response("Finished");
    }

    public function onCancel()
    {
        $this->response("Cancelled");
    }

}
```

Inputs:
```php
protected $inputs = [
    'amount',
    'submit',
];

public function amount(Input $input);

public function submit(Input $input);
```

Path:
```php
protected $path = [
    'amount',
    'submit',
];
```

#### Settings
```php
protected $cancelKey = false;
protected $defaultFormKey = false;
protected $previousKey = true;
protected $skipKey = true;
protected $mirrorKey = true;

protected $previousKey = "<< Back <<";
```

#### Attributes
```php
public function amount(Input $input);

#[AsAttribute]
public $customAttr;

#[AsAttribute]
#[FindById]
public BotUser $user;

public function onFinish()
{
    $amount = $this->amount;
    $customAttr = $this->customAttr;
    $user = $this->user;
}
```

#### Requests
```php
TestForm::make()->request();
// Or
TestForm::make()->request([
    'customAttr' => 'Test',
    'user' => $user,
]);
// Or
TestForm::make([
    'customAttr' => 'Test',
    'user' => $user,
])->request();
```

### Form Has Chunk
```php
class TestForm extends Form
{
    use HasFormChunks;
}
```

#### Inputs
```php
protected $inputs = [
    'name',
    'bankChunk' => [
        'card',
        'amount',
    ],
];
```

#### Requests
```php
TestForm::make()->requestChunk('bankChunk');
TestForm::make()->requestChunk(['name', 'bankChunk']);
TestForm::make(['user' => $user])->requestChunk('name');
TestForm::make()->requestChunk('name', [
    'user' => $user,
]);
TestForm::make(['user' => $user])->withChunk('name')->request();
```

### Input
#### Request Message
```php
$input->prompt("Enter number:");
// Or
$input->ask("Enter number:");
```

#### Keyboards
```php
$input->options([
    [ $input->key("Hello world") ],
]);
$input->header([
    [ $input->key("Page 2/10") ],
]);
$input->footer([
    [ $input->key("Next Page") ],
]);
```

#### Filters
```php
$input->textSingleLine()
$input->unsignedInt()
$input->min(100)
$input->minLength(5)
$input->clamp(100, 999)
$input->length(5, 1000)
$input->divisible(1000)
$input->messageBuilder()
```

#### Advanced Filters
```php
$input->useFilter(
    fn(Filter $filter) => $filter
        ->textSingleLine()
        ->regex('/^Token: (.+)$/', 1)
        ->or()
        ->unsignedInt()
        ->clamp(1, 1000)
)
```

#### Events
```php
$input
    ->passing(function ($update) {})
    ->then(function () {})
    ->filled(function() {})
    ->on('customEvent', function() {})
```

#### Settings
```php
$input
    ->disableCancelKey()
    ->disableDefaultFormKey()
    ->disableMirrorKey()
    ->disablePreviousKey()
    ->disableSkipKey()
    
    ->cancelKey()
    ->defaultFormKey()
    ->mirrorKey()
    ->previousKey()
    ->skipKey()
    
    ->cancelKey("Back To Main Menu")
```

### Form Key
```php
FormKey::make('Test');
// Or
$input->key('Test');
```

#### Replacement
```php
$input->key('Test', 'Replacement');
```

#### Action
```php
$input->keyAction('Click me', fn() => $this->response("Clicked"))
// Or
$input->keyAction('Click me', 'customEvent')
$input->on('customEvent', fn() => $this->response("Clicked"))
```

#### Action With Pass Value
```php
$input->keyAction('Click me', fn($pass) => $pass("Hello World"))
```

#### Enabled Condition
```php
$input->key('Some key')->when($id == 3)
$input->key('Some key')->unless($id != 3)
```

### Command
```php
class BalanceCommand extends CommandAction
{

    protected $command = '/balance';

    public function handle()
    {
        $this->response("Your balance: $0");
    }

}
```

#### Command Value
```php
protected $command = '/balance';
protected $command = '/balance {user:any}';
protected $command = ['/balance', '/myAccount'];
```

#### Parameter
```php
protected $command = '/balance {user:any}';

public function handle($user)
{
    $this->response("You entered: $user");
}
```

#### Separate Method
```php
protected $command = [
    '/balance',
    '/balance {user:any}' => 'handleFor',
];

public function handle()
{
    $this->response("Your balance: $0");
}

public function handleFor($user)
{
    $this->response("You entered: $user");
}
```

#### Start Command
```php
class StartCommand extends StartCommandAction
{

    protected $command = ['/start', '/start {code:any?}' => 'invite'];

    protected $ignoreSpaces = true;

    public function handle()
    {
        HomeSection::make()->main();
    }
    
    public function invite($code)
    {
        if ($code != User::current()->id && ($target = User::findCache($code)))
        {
            $target->inviteAccepted(User::current());
        }

        $this->handle();
    }

}
```

### Handle Callback
```php
class BalanceSection extends Section
{
    use CallbackControl;
}
```

#### Auto Match
If you don't define `onCallback` method, it will be automatically!
```php
#[OnCallback]
public function balance()
{
    $this->tell("Your balance: $0");
}
```

Custom pattern:
```php
#[OnCallback('myBalance')]
public function balance();
```

Full pattern:
```php
#[OnCallback('balance', true)]
public function balance();
```

Full pattern with method name (_ replaced with method name or $name argument)
```php
#[OnCallback('myMethod:{_}', true)]
public function balance();
```

Custom pattern with method name:
```php
#[OnCallback('{_}:{id:any}', true)]
public function balance($id);
```

#### Custom Match
Define the `onCallback` method:

```php
public function onCallback(QueryMatcher $matcher)
{
    $matcher->match('balance', 'balance');
}

public function balance()
{
    $this->tell("Your balance: $0");
}
```

Enable auto matching:
```php
public function onCallback(QueryMatcher $matcher)
{
    $matcher->match('balance', 'balance');
    $matcher->autoMatch($this);
}
```

#### Inline Key
```php
$this->keyInline("Key Text", ...args);
```

Inline for auto matched method:
```php
$this->keyInline("Text", 'method', ...args);
```

Inline for customized auto matched method:
(In this example, we don't have any matching parameter)
```php
#[OnCallback('balance')]
public function balance();

public function main()
{
    $key = $this->keyInline('My Balance');
}
```
Or use {_} to identify method:
```php
#[OnCallback('{_}')]
public function balance();

public function main()
{
    $key = $this->keyInline('My Balance', 'balance');
}
```

Pass arguments:
```php
#[OnCallback]
public function balance($id);

public function main()
{
    $id = 1000;
    $key = $this->keyInline('My Balance', 'balance', $id);
}
```
In customized method:
```php
#[OnCallback('{_}:{id:any}')]
public function balance($id);

public function main()
{
    $id = 1000;
    $key = $this->keyInline('My Balance', 'balance', $id);
}
```

Usage:
```php
public function main()
{
    $this->response("Select:", key: [
        [ $this->keyInline("My Balance", 'balance') ],
    ]);
}
```

### Middle Action
```php
class EnterAgeMiddleAction extends MiddleAction
{
    public function isRequired()
    {
        return !BotUser::current()->age;
    }

    public function main()
    {
        $this->inlineForm('ageForm')->request();
    }

    public function ageForm(InlineForm $form)
    {
        $form
            ->input('age', fn (Input $input) => $input
                ->unsignedInt()->clamp(10, 150)
                ->prompt("Enter your age:")
            )
            ->cancel(fn() => HomeSection::invokes('main'))
            ->finish(function (Form $form)
            {
                BotUser::current()->update(['age' => $form->age]);
                $this->redirect();
            });
    }
}
```

#### Request
Request runs anyway
```php
public function enterAge()
{
    EnterAgeMiddleAction::request([static::class, 'ageEntered']);
}

public function ageEntered()
{
    $this->response("Good Job");
    $this->main();
}
```

#### Required
Required runs when class is required (and stop the code)
```php
public function withdraw()
{
    EnterAgeMiddleAction::requiredHere();

    if (BotUser::current()->age < 20)
    {
        $this->response("Access Denied");
        return;
    }
    
    $this->inlineForm('withdrawForm')->request();
}
```
> Note: `requiredHere` uses backtrace and detected current method! So don't use it on helper functions.
```php
public function withdraw()
{
    EnterAgeMiddleAction::required([static::class, 'withdraw']);

    if (BotUser::current()->age < 20)
    {
        $this->response("Access Denied");
        return;
    }
    
    $this->inlineForm('withdrawForm')->request();
}
```

#### Arguments
```php
class TestAction extends MiddleAction
{
    public function main(BotUser $user)
    {
        $this->inlineForm('myForm', user: $user)->request();
    }

    public function myForm(InlineForm $form, #[FindById] User $user);
}

class AnotherClass extends Section
{
    public function withdraw()
    {
        TestAction::requiredHere($user);
    }
}
```

#### Required Globally
Add class name to update handlers:
```php
class PrivateHandler extends UpdateHandler
{
    public function handle(HandlerFactory $handler)
    {
        $handler
            ->handle([
                MiddleActions\EnterAgeMiddleAction::class,
                $handler->afterMiddles(Sections\HomeSection::class, 'main'),
    
                $handler->step(),
            ])
        ;
    }
}
```
Mmb support multiple middle actions:
```php
class PrivateHandler extends UpdateHandler
{
    public function handle(HandlerFactory $handler)
    {
        $handler
            ->handle([
                MiddleActions\EnterAgeMiddleAction::class,
                MiddleActions\EnterNameMiddleAction::class,
                MiddleActions\EnterPhoneMiddleAction::class,
                $handler->afterMiddles(Sections\HomeSection::class, 'main'),
    
                $handler->step(),
            ])
        ;
    }
}
```
Set which method should run after the middle actions handled (and redirected):
```php
$handler->afterMiddles(Sections\HomeSection::class, 'main'),
```
Set different methods for different middle actions:
```php
class PrivateHandler extends UpdateHandler
{
    public function handle(HandlerFactory $handler)
    {
        $handler
            ->handle([
                MiddleActions\EnterAgeMiddleAction::class,
                MiddleActions\EnterNameMiddleAction::class,
                MiddleActions\EnterPhoneMiddleAction::class,
                $handler->afterMiddles(Sections\HomeSection::class, 'ageEntered')
                    ->only(MiddleActions\EnterAgeMiddleAction::class),
                $handler->afterMiddles(Sections\HomeSection::class, 'main'),
    
                $handler->step(),
            ])
        ;
    }
}
```
Category:
```php
class EnterAgeMiddleAction extends MiddleAction
{
    protected $category = 'user-profile';
}
class EnterNameMiddleAction extends MiddleAction
{
    protected $category = 'user-profile';
}
class EnterPhoneMiddleAction extends MiddleAction
{
    protected $category = 'phone';
}

class PrivateHandler extends UpdateHandler
{
    public function handle(HandlerFactory $handler)
    {
        $handler
            ->handle([
                MiddleActions\EnterAgeMiddleAction::class,
                MiddleActions\EnterNameMiddleAction::class,
                MiddleActions\EnterPhoneMiddleAction::class,
                $handler->afterMiddles(Sections\HomeSection::class, 'profileUpdated')->for('user-profile'),
                $handler->afterMiddles(Sections\HomeSection::class, 'phoneUpdated')->for('phone'),
                $handler->afterMiddles(Sections\HomeSection::class, 'main'), // Will not run in this case
    
                $handler->step(),
            ])
        ;
    }
}
```
> Note: `afterMiddles` runs just one method! How it selects? When last middle actions run, first `afterMiddles` that match the middle action will run. 

### Resource Section
```php
class BotUserResourceSection extends ResourceSection
{

    protected $for = BotUser::class;

    public function resource(ResourceMaker $maker)
    {
        $maker->list()
            ->label(fn (BotUser $record) => "#{$record->id} ($record->name)")
            ->creatable(
                fn (ResourceCreateModule $module) => $module
                    ->textSingleLine('name', "نام کاربر را وارد کنید:")
                    ->attribute('step', '')
            )
            ->searchable(
                fn (ResourceSearchModule $module) => $module
                    ->by('id')
                    ->message("آیدی کاربر را جستجو کنید:")
            )
        ;
        $maker->info()
            ->editable(
                fn (ResourceEditModule $module) => $module
                    ->textSingleLine('name', "نام جدید کاربر را وارد کنید:")
            )
            ->editableSingle("نام", 'name', left: true)
            ->deletable()
        ;
    }
    
}
```

Be pretty:
```php
class BotUserResourceSection extends ResourceSection
{

    protected $for = BotUser::class;

    public function resource(ResourceMaker $maker)
    {
        $this->list($maker->list());
        $this->info($maker->info());
    }

    public function list(ResourceListModule $module)
    {
        $module
            ->label(fn (BotUser $record) => "#{$record->id} ($record->name)")
            ->creatable($this->create(...))
            ->searchable($this->search(...))
        ;
    }

    public function info(ResourceInfoModule $module)
    {
        $module
            ->editable($this->edit(...))
            ->editableSingle("نام", 'name', left: true)
            ->deletable()
        ;
    }

    public function create(ResourceCreateModule $module)
    {
        $module
            ->textSingleLine('name', "نام کاربر را وارد کنید:")
            ->attribute('step', '')
        ;
    }

    public function edit(ResourceEditModule $module)
    {
        $module
            ->textSingleLine('name', "نام جدید کاربر را وارد کنید:")
        ;
    }

    public function search(ResourceSearchModule $module)
    {
        $module
            ->by('id')
            ->message("آیدی کاربر را جستجو کنید:")
        ;
    }

}
```

#### Resource List
A list to show all records.
```php
$maker->list()
    // Set message
    ->message("List:")
    // Set key label:
    ->label(fn (BotUser $record) => "#{$record->id} ($record->name)")
    // Add create key:
    ->creatable($this->create(...))
    // Add search key:
    ->searchable($this->search(...))
    // Add order key:
    ->orderable($this->order(...))
    // Customize query:
    ->query(fn () => BotUser::query()->role('admin'))
    // Customize query 2:
    ->filter(fn ($builder) => $builder->role('admin'))
    // Custom key:
    ->addTopKey("My Key", fn () => $this->response("Hey!"))
    // Add simple filters:
    ->simpleFilter('testFilter', $this->testFilter(...))
    // Customize empty-state
    ->notFound(fn () => $this->response("Empty state"))
    ->notFoundLabel("Empty")
    // Select action
    ->select(fn ($record) => $this->response("Selected #{$record->id}"))
    // Customize menu
    ->top(fn (Menu $menu) => ...)
    ->bottom(fn (Menu $menu) => ...)
    // Customize paginate
    ->perPage(20)
    ->pageHide()
    ->paginateHide()
    ->pageLabel(fn ($page, $lastPage) => "Page $page/$lastPage")
    ->paginateRow(...)
    ->paginateOnTop()
    ->paginateOnBottom()
    ->paginateOnBoth()
;
```

Available parameters:
```php
function action(
    $record, // Current record (only when one record is using)
    $all, $paginate, // All records in current page
    $page, $current, // Current page number
    $lastPage, // Last page number
    $menu, // Current menu (only for top() and bottom())
    $module, // Module
)
```

#### Resource Info
```php
$maker->info()
    // Set message
    ->message(fn ($record) => "User #{$record->id}:")
    // Add edit key
    ->editable($this->edit(...))
    // Add single attribute edit key
    ->editableSingle("نام", 'name', left: true)
    // Add delete key
    ->deletable($this->delete(...))
    // Add key
    ->schema(...)
;
```

Available parameters:
```php
function action(
    $record, // Current record
    $module, // Module
)
```

#### Resource Create/Edit
```php
$module
    // Add input
    ->input(...)
    ->textSingleLine('name', "نام کاربر را وارد کنید:")
    // Set custom attribute value
    ->attribute('step', '')
    ->attributes(['step' => ''])
    // Events
    ->creating(...)
    ->created(...)
    ->createdOpenInfo('info')
;
```

Available parameters:
```php
function action(
    $record, // Current record
    $module, // Module
)
```

#### Resource Search
```php
$module
    // Set key
    ->by('id')
    // Set message
    ->message("آیدی کاربر را جستجو کنید:")
    // Search key
    ->keyLabel("Search")
    ->keySearchingLabel(fn ($query) => "Searching [$query]")
    // "Show All" key
    ->allKeyEnabled()
    ->allKeyLabel("Show All")
    // Set algorithm
    ->simple()
    ->query(fn ($builder, $query, $key))
;
```

#### Resource Simple Filter
```php
$module
    // Reset filters
    ->reset()
    // Set message
    ->message(...)
    // Set key
    ->keyLabel("My Filter")
    ->prefix("Prefix")
    ->suffix("Suffix")
    // Add filters
    ->addNone("None", default: true)
    ->add("Admins", fn ($builder) => $builder->role('admin'))
    // Toggle mode
    // When press filter in the list, automatically select next filter
    ->toggle()
;
```

Set key label dynamically:
```php
$module
    ->prefix("My Filter : ")
    ->keyLabelAuto()
```

#### Resource Order
Order is an simple filter.
```php
$module
    // Delete default orders
    ->reset()
    // Set message
    ->message(...)
    // Add order
    ->addOrder("Name", 'name', asc: true)
    ->addAsc("Name Asc", 'name')
    ->addDesc("Name Desc", 'name')
;
```

#### Resource Delete
```php
$module
    // Set message
    ->message("Are you sure to delete?")
    // Set confirm message
    ->confirm("Yes, Delete!")
```


## Inline Actions
All inline actions should define in class of type Action, as a function.

### Menu
```php
public function mainMenu(Menu $menu)
{
    $menu
        ->schema([
            [
                $menu->key("Hello World!", 'helloWorld'),
            ],
        ])
    ;
}
```
#### Advanced Key
```php
public function mainMenu(Menu $menu)
{
    $menu
        ->schema([
            [
                // Invoke helloWorld() method
                $menu->key("Hello World!", 'helloWorld'),
                
                // Invoke helloWorld() method from other class
                $menu->key("Profile")->invoke(ProfileSection::class, 'main'),
                $menu->keyFor("Profile", ProfileSection::class, 'main'),
                
                // Inline action
                $menu->key("Help", fn() => $this->response("Information Text...")),
                
                // Inline action (way 2)
                $menu->key("Logout", 'logout'), // logout action defined below
                
                // Invoke with arguments
                $menu->key("Plus", 'addValue', 1),
                $menu->key("Mines", 'addValue', -1),
                
                // Add condition
                $menu->key("My Key")->if($id == 3),
                $menu->key("Invalid")->else(),
                $menu->key("My Key")->visibleIf($id == 3),
                $menu->key("Invalid")->visibleElse(),
                
                // Visible / Hidden
                $menu->key("Visible")->visible(),
                $menu->key("Hidden")->hidden(),
                
                // Other types
                $menu->key("Contact")->requestContact(),
                $menu->key("Location")->requestLocation(),
                $menu->key("Chat")->requestChat(1),
            ],
        ])
        ->on('logout', function()
        {
            $this->response("You're logged out!");
        })
    ;
}
```
#### Regex Pattern
```php
public function mainMenu(Menu $menu)
{
    $menu
        ->schema([...])
        ->onRegex('/^My name is (\w+)$/', function ($matches)
        {
            $this->response("Hello {$matches[1]}");
        })
        // Or
        ->onRegex('/^My name is (\w+)$/', function ($name)
        {
            $this->response("Hello {$name}");
        }, 1)
    ;
}
```
#### Default Message
```php
public function mainMenu(Menu $menu)
{
    $menu
        ->schema([...])
        ->message("Welcome =D")
    ;
}
```

### Inline Form
```php
public function testForm(InlineForm $form)
{
    $form
        ->input('amount', fn (Input $input) => $input
            ->unsignedInt()
            ->prompt("Enter amount:")
        )
        ->input('submit', fn (Input $input) => $input
            ->onlyOptions()
            ->options([[$input->key("Submit", true)]])
            ->prompt("Submit?")
        )
        ->cancel(fn() => $this->main())
        ->finish(function (Form $form)
        {
            $this->response("Form Submitted");
        })
    ;
}
```

Usage:
```php
public function main()
{
    $this->inlineForm('testForm')->request();
}
```

#### Scope
Ready delete form:
```php
public function testForm(InlineForm $form)
{
    $form->deleteScope();
}
```

Custom scope:
```php
class IFMyScope extends InlineFormScope
{
    public function apply(InlineForm $form)
    {
        $form
            ->input('submit', fn (Input $input) => $input
                ->onlyOptions()
                ->add("Submit", true)
                ->prompt("Submit?")
            )
        ;
    }
}
```

Usage:
```php
public function testForm(InlineForm $form)
{
    $form
        ->input('amount', fn (Input $input) => $input
            ->unsignedInt()
            ->prompt("Enter amount:")
        )
        ->scope(new IFMyScope())
        ->cancel(fn() => $this->main())
        ->finish(function (Form $form)
        {
            $this->response("Form Submitted");
        })
    ;
}
```

### Dialog
```php
public bool $enabled = false;
public function toggleDialog(Dialog $dialog)
{
    $dialog
        ->use(DialogData::class)
        ->with('enabled')
        ->schema([
            [ $dialog->key("Status: " . ($this->enabled ? "Enabled" : "Disabled")) ],
            [ $dialog->key("Toggle", 'toggle') ],
        ])
        ->on('toggle', function() use($dialog)
        {
            $this->tell("Toggled!");
            $this->enabled = !$this->enabled;
            $dialog->reload();
        })
        ->message("Toggle Dialog [" . ($this->enabled ? "Enabled" : "Disabled") . "]");
}
```

#### Key Id
Default id is key text, for example:
```php
$dialog->key("Toggle") // id = Toggle
```
Set custom id:
```php
$dialog->key("Toggle")->id('tg') // id = tg
```
Set action and id easily:
```php
$dialog->keyId("Info", 'info') // id = info, action = info
```
For identify pressed key, id should be unique.
```php
$dialog->schema(function()
{
    foreach ($users as $user)
    {
        // All of these keys have same response, so use `keyId`:
        yield [ $dialog->keyId("User: {$user->name}", 'none') ];
        
        // Each key must run different action, but has same text! So set unique id:
        yield [ $dialog->key("Delete", 'delete', $user)->id("del-{$user->id}") ]; 
    }
});
```


#### Reload
Manually reload dialog:
```php
$dialog->on('toggle', function() use($dialog)
{
    $this->tell("Toggled!");
    $this->enabled = !$this->enabled;
    $dialog->reload();
})
```

Reload dialog after each action:
```php
$dialog->autoReload();
```

Cancel auto reloading with:
```php
$dialog->on('status', function() use($dialog)
{
    $this->tell("Status: " . ($this->enabled ? "Enabled" : "Disabled"));
    $dialog->cancelReload();
})
```

#### Data Model
```php
$dialog->use(DialogData::class);
```

Model:
```php
class DialogData extends Model
{
    use HasFactory, HasPresent;

    public function present(Present $present)
    {
        $present->id();
        $present->belongsTo(BotUser::class, 'user_id'); // Important
        $present->text('target')->cast(StepCasting::class)->nullable(); // Important
        $present->timestamps();
    }
}
```

#### Example
```php
public int $number = 0;
public function selectNumber(Dialog $dialog)
{
    $dialog
        ->use(DialogData::class)
        ->with('number')
        ->schema([
            [ $dialog->key("> {$this->number} <") ],
            [
                $dialog->key("1", 'pass', 1),
                $dialog->key("2", 'pass', 2),
                $dialog->key("3", 'pass', 3),
                $dialog->key("<", 'backspace'),
            ],
            [
                $dialog->key("4", 'pass', 4),
                $dialog->key("5", 'pass', 5),
                $dialog->key("6", 'pass', 6),
                $dialog->key("C", 'clear'),
            ],
            [
                $dialog->key("7", 'pass', 1),
                $dialog->key("8", 'pass', 2),
                $dialog->key("9", 'pass', 3),
                $dialog->key("0", 'pass', 0),
            ],
        ])
        ->autoReload()
        ->on('pass', fn($number) => $this->number = $this->number * 10 + $number)
        ->on('backspace', fn() => $this->number = floor($this->number / 10))
        ->on('clear', fn() => $this->number = 0)
        ->message("Select number:")
    ;
}
```

### Fixed Dialog
```php
class ProfileSection extends Section
{
    use CallbackControl;
    
    #[FixedDialog('profile:{user:int}')]
    public function userProfile(Dialog $dialog, #[FindById] BotUser $user)
    {
        $dialog
            ->schema([
                [ $dialog->key("Info", fn() => $this->tell("User: {$user->name}")) ],
                [ $dialog->key("Add 500", 'add', 500) ],
            ])
            ->on('add', function ($count) use ($user)
            {
                $user->money += $count;
                $user->save();
                $this->tell("Added!");
            })
            ->message("Edit User {$user->id}:")
        ;
    }
}
```

> Warning: Telegram just accept 64 characters! So be careful in define variants and callback name.

#### Variant
If you have a variant in dialog, you should define it in `FixedDialog` value:
```php
#[FixedDialog('profile:{user:int}')]
public function userProfile(Dialog $dialog, #[FindById] BotUser $user);
```

#### Within
```php
public int $myVar;

#[FixedDialog('profile:{myVar:int}')]
public function userProfile(Dialog $dialog)
{
    $dialog->with('myVar');
}
```

#### Argument
It's normal!
```php
$dialog->key("Something", 'foo', 1000, false, [], $user);
```

### Base
#### Events
```php
public function mainMenu(Menu $menu)
{
    $menu
        ->creating(fn() => $this->response("Menu is creating..."))
        ->loading(fn() => $this->response("Menu is loading..."))
    ;
}
```
#### Variant history
+ 1- Argument history
```php
public function mainMenu(Menu $menu, int $number, #[FindById] Post $dbPost, Post $serializedPost)
```
If you define a Model like Post, you have two options.
1- Using #[FindById] to save just `id` and reload model from database.
2- Empty using to save all attributes in user data (will not reload from database).

Usage:
```php
public function main()
{
    $dbPost = Post::find(27);
    $sPost = new Post(['title' => 'Hello world']);
    $this->menu('mainMenu', number: 100, dbPost: $dbPost, serializedPost: $sPost)->response();
}
```

+ 2- Property history
```php
public int $number;
#[FindById]
public Post $dbPost;
public Post $serializedPost;

public function mainMenu(Menu $menu)
{
    $menu
        ->with('number', 'dbPost', 'serializedPost')
    ;
}
```
Usage:
```php
public function main()
{
    $this->number = 100;
    $this->dbPost = Post::find(27);
    $this->serializedPost = new Post(['title' => 'Hello world']);
    
    $this->menu('mainMenu')->response();
}
```

'


## Handlers
```php
class PrivateHandler extends UpdateHandler
{

    public function handle(HandlerFactory $handler)
    {
        $handler
            ->match($this->update->getChat()?->type == 'private')
            ->recordUser(
                BotUser::class,
                $this->update->getUser()?->id,
                create: $this->createUser(...),
                validate: $this->validateUser(...),
                autoSave: true,
            )
            ->handle([
                Commands\StartCommand::class,
                $handler->inherit(),
                $handler->step(),
            ])
        ;
    }

    public function createUser()
    {
        $user = $this->update->getUser();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'step' => '',
        ];
    }

    public function validateUser(BotUser $user)
    {
        // return $user->ban === false || $user->can('IgnoreBan');
        return true;
    }

}
```

#### Example
```php
class ChannelPostHandler extends UpdateHandler
{

    public function handle(HandlerFactory $handler)
    {
        $handler
            ->match((bool) $this->update->channelPost)
            ->handle([
                PostHandling::class,
            ])
        ;
    }

}
```

#### Custom Handling
```php
class PostHandling extends Section implements UpdateHandling
{
    public function handleUpdate(Update $update)
    {
        if ($update->channelPost->id != MY_CHANNEL)
        {
            $update->skipHandler();
            return;
        }
        
        // Handle
    }
}
```

### Extends Handler

Use the `Handler` facade in service providers, to extends the handlers

#### Add Update Handler
```php
Handler::add(MyCustomHandler::class);
```

#### Extend

By using `extend`, you can extend an existing handler

```php
Handler::extend(PrivateHandler::class, function (HandlerExtends $extends)
{
    $extends
        ->first(
            fn (HandlerFactory $handler) => $handler
                ->record(...)
                ->invoke(...)
        )
        ->last(
            fn (HandlerFactory $handler) => $handler
                ->record(...)
                ->invoke(...)
        )
        ->event(
            'custom',
            fn (HandlerFactory $handler) => $handler
                ->record(...)
                ->invoke(...)
        )
        ->handle(fn (HandlerFactory $handler) => [
            CustomHandling::class,
        ])
        ->handle(fn (HandlerFactory $handler) => [
            CustomHandling2::class,
        ], 'custom-name')
})
```

And use in the handler:

```php
class PrivateHandler extends UpdateHandler
{

    public function handle(HandlerFactory $handler)
    {
        $handler
            // Here automatically fire the "first"
            ->match($this->update->getChat()?->type == 'private')
            ->extends('custom')
            ->recordUser(
                BotUser::class,
                $this->update->getUser()?->id,
                create: $this->createUser(...),
                validate: $this->validateUser(...),
                autoSave: true,
            )
            // Here automatically fire the "last"
            ->handle([
                $handler->inherit('custom-name'),
                StartCommandHandler::class,
                $handler->inherit(),
                
                $handler->step(),
            ])
        ;
    }

}
```

### Handle Service Provider

```php
use Mmb\Support\Providers\HandleServiceProvider as ServiceProvider;

class HandleServiceProvider extends ServiceProvider
{

    /**
     * List of the handlers
     */
    protected array $handlers = [
        CustomHandler::class,
    ];

    /**
     * Map of extended handler and callback method name
     */
    protected array $extend = [
        PrivateHandler::class => 'extendPrivate',
    ];
    
    public function extendPrivate(HandlerExtends $extends)
    {
        $extends->handle(fn (HandlerFactory $handler) => [
            CustomCommand::class,
        ], 'commands')
    }
    
    /**
     * List of areas
     */
    protected array $areas = [
        CustomArea::class,
    ];

}
```


## Query Matcher
```php
public function onCallback(QueryMatcher $matcher)
{
    $matcher->match('balance', 'balance');
}
```

### Pattern
Simple text:
```php
$matcher->match('my text');
```

#### Argument
```php
$matcher->match('My name is {name}');
```

#### Argument Type
```php
$matcher->match('/setAge {age:int}');
```

Valid types:

| Type      | Real Pattern    |
|-----------|-----------------|
| `slug`    | `[^\s\n\r\t]+`  |
| `slug?`   | `[^\s\n\r\t]*`  |
| `inline`  | `.+`            |
| `inline?` | `.*`            |
| `any`     | `[\s\S]+`       |
| `any?`    | `[\s\S]*`       |
| `int`     | `\d+`           |
| `int?`    | `\d*`           |
| `number`  | `\d+\.?\d*`     |
| `number?` | `(\d+\.?\d*\|)` |

Note: `slug` is default type.

#### Nullable Type
Add `?` after argument type:
```php
$matcher->match('{age:int?}');
```


### Match
```php
$matcher->match('pattern')
```

#### Action
```php
$matcher->match('pattern')->action('myMethod');
```

#### Action From
```php
$matcher->match('pattern {method}')->actionFrom('method');
```

#### Json
```php
$matcher->match('{method}:{args}')->json('args')->actionFrom('method');
```
All the input arguments will be compressed in {args} parameter.
And all the matched arguments named {args} will be uncompressed in action parameters.

#### Options
Ignore case:
```php
$matcher->match('IP')->ignoreCase(); // Accept 'IP', 'ip', 'Ip' and 'iP'
```

Whitespace:
```php
$matcher->match('hi mmb')->ignoreSpaces(); // Accept 'hi mmb', 'hi  mmb', 'hi   mmb', 'hi \n mmb' and ...
$matcher->match('hi mmb')->optionalSpaces(); // Accept 'himmb', 'hi mmb', 'hi  mmb', 'hi \n mmb' and ...
```

Custom spaces pattern:
```php
$matcher->match('hi mmb')->spaces('_+'); // Accept 'hi_mmb', 'hi__mmb', 'hi___mmb' and ...
```

### Special Value
#### Regex Pattern
```php
$matcher->match('{name}')->pattern('name', '\w{5}'); // Accept only words with 5 letters
```

#### Same
```php
$matcher->match('{name}')->same('name', 'Mahdi'); // Accept just 'Mahdi'
```

#### In
```php
$matcher->match('{name}')->in('name', ['Mahdi', 'Mmb']); // Accept 'Mahdi' and 'Mmb'
```

### Auto Match
```php
public function onCallback(QueryMatcher $matcher)
{
    $matcher->autoMatch($this);
}

#[OnCallback]
public function balance()
{
    $this->tell("Your balance: $0");
}
```

Note: `#[OnCallback]` only works in `CallbackControl` objects.


### Advanced
#### Find Pattern
Find first pattern that matched with input text
```php
$pattern = $matcher->findPattern('My name is Mahdi');

if ($pattern)
{
    $pattern->invoke();
}
```

#### Make Query
Make query using parameters (first matched pattern will use)
```php
$matcher->match('My name is {name}');

$text = $matcher->makeQuery('Mahdi');
echo $text; // My name is Mahdi
```


## Area
```php
class AdminArea extends Area
{

    public function boot()
    {
        $this->authClass(EditProfileSection::class, 'AccessPanel');
        $this->authNamespace('App\Mmb\Sections\Panel', 'AccessPanel');
        $this->backUsingForNamespace('App\Mmb\Sections\Panel', PanelSection::class, 'main');
    }

}
```

```php
class AdminArea extends Area
{

    protected string $namespace = 'App\Mmb\Sections\Panel';

    public function boot()
    {
        $this->auth('AccessPanel'); // For the namespace
        $this->authNamespace('Profile', 'AccessProfile'); // For the Panel\Profile namespace
        $this->authClass('PanelSection', 'AccessPanel2'); // For the Panel\PanelSection class
        $this->backUsing(PanelSection::class, 'main');
    }

}
```

To define an area, add it to `config/mmb.php`:
```php
'areas' => [
    Areas\AdminArea::class,
],
```


## Multiple Bot
Single source, multiple bot! Change the `configs/mmb.php` file.

### Default Channeling
Use this for single bot:
```php
return [
    'channeling'     => Core\DefaultBotChanneling::class,

    'channels'       => [
        'default' => [
            'token'     => env('BOT_TOKEN'),
            'username'  => env('BOT_USERNAME'),
            'hookToken' => 'YOUR_HOOK_TOKEN',
            // 'guard'     => 'bot',

            'handlers' => [
                Handlers\PrivateHandler::class,
                Handlers\GroupHandler::class,
            ],
        ],
    ],
];
```

Or for multiple bots:
```php
return [
    'channeling'     => Core\DefaultBotChanneling::class,

    'channels'       => [
        'default' => [
            'token'     => env('BOT_TOKEN'),
            'username'  => env('BOT_USERNAME'),
            'hookToken' => 'YOUR_HOOK_TOKEN',
            // 'guard'     => 'bot',

            'handlers' => [
                Handlers\PrivateHandler::class,
                Handlers\GroupHandler::class,
            ],
        ],
        'second' => [
            'token'     => 'SECOND_TOKEN',
            'username'  => 'SECOND_USERNAME',
            'hookToken' => 'SECOND_HOOK_TOKEN', // should different!
            // 'guard'     => 'bot',

            'handlers' => [
                Handlers\PrivateHandler::class,
                Handlers\GroupHandler::class,
            ],
        ],
    ],
];
```

### Creative Channeling
Creative channeling use a database to find robot. So you can create multiple bots in one source code and modify bots in real time.
```php
return [
    'channeling'         => Core\CreativeBotChanneling::class,

    'channels'           => [
        'database' => [
            'model'          => BotModel::class,
            'tokenColumn'    => 'token', // default is 'token'
            'nameColumn'     => 'name', // default is 'name'
            'hookColumn'     => 'hook_token', // should be unique! default is 'hook_token'
            'usernameColumn' => 'username', // optional
        ],
        'default' => [
            'token'     => env('BOT_TOKEN'),
            'username'  => env('BOT_USERNAME'),
            'hookToken' => 'YOUR_HOOK_TOKEN',

            'handlers' => [
                Handlers\PrivateHandler::class,
                Handlers\GroupHandler::class,
            ],
        ],
        'foo_cat' => [
            'handlers' => [
                FooHandlers\PrivateHandler::class,
            ],
        ],
    ],
];
```


## Role & Permissions
We recommend the `spatie/laravel-permission` package.

### Install Package
```shell
composer require spatie/laravel-permission
```

### Setup Model
Add `HasRoles` and `$guard_name` to `BotUser`:
```php
class BotUser extends Authenticatable implements Stepping
{
    use HasRoles;
    
    protected $guard_name = 'bot';
}
```

### Setup Seeder
> This is optional. You can use this template to be faster.

Create a seeder in `database/seeders` like `RoleSeeder` and use this template:
```php
<?php

namespace Database\Seeders;

use App\Models\BotUser;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{

    public string $guardName = 'bot';

    public array $permissions = [
        'AccessPanel',
        'ModifyAdmins',
    ];

    public array $roles = [
        'SuperAdmin' => '*',
        'Admin' => ['AccessAdmin'],
    ];

    public array $fixedUsers = [
        '370924007' => 'SuperAdmin',
    ];

    public function run(): void
    {
        foreach ($this->permissions as $permission)
        {
            Permission::findOrCreate($permission, $this->guardName);
        }

        foreach ($this->roles as $role => $permissions)
        {
            if ($permissions == '*') $permissions = $this->permissions;
            elseif (is_string($permissions)) $permissions = [$permissions];

            Role::findOrCreate($role, $this->guardName)->syncPermissions($permissions);
        }

        foreach ($this->fixedUsers as $user => $roles)
        {
            if ($roles === null) $roles = [];
            elseif (is_string($roles)) $roles = [$roles];

            BotUser::find($user)?->syncRoles($roles);
        }
    }
}
```

### Setup Area
Create Aria like `AdminArea` in `App\Mmb\Areas` using:
```shell
php artisan make:area AdminArea
```
And define roles in `boot()`:
```php
public function boot()
{
    $this->forNamespace('App\\Mmb\\Sections\\Panel', 'AccessAdmin');
}
```

### Introduce Area
Add area class to `config/mmb.php`:
```php
'areas' => [
    Areas\AdminArea::class,
],
```


## POV
If you want to answer other users in the update handling, you can change POV
to second user and then response that. Why?

If you want to send a menu to first user:
```php
$this->menu('myMenu')->response();
```
That's easy! But what about second user? That's should be hard.

Don't worry! POV is here!
```php
pov()->user($secondUser)->run(function ()
{
    static::make()->menu('myMenu')->response();
});
```
That's so easy!

Example:
```php
class ChatQueueSection extends Section
{
    public function findChat()
    {
        if ($queue = ChatQueue::first())
        {
            $queue->delete();
            $chat = Chat::create([
                'user_a_id' => BotUser::current()->id,
                'user_b_id' => $queue->user_id,
            ]);

            pov()->user($queue->user)->run(
                fn () => static::invokes('openedChat', $chat)
            );
            static::invokes('openedChat', $chat);
        }
        else
        {
            ChatQueue::create(['user_id' => BotUser::current()->id]);
            $this->response("You are in queue...");
        }
    }

    public function openedChat(Chat $chat)
    {
        $this->menu('chatMenu', chat: $chat)->response();
    }

    public function chatMenu(Menu $menu, #[FindById] Chat $chat)
    {
        $menu
            ->message("A user found:")
            ->schema([
                [ $menu->key("Exit", 'exit') ],
            ])
        ;
    }
}
```

Note: If you use POV, you should make new class and then use that methods.

That's because the current Section is using old user data like Update,
`with` values, and ...

So you can make new instance for new POV like this:
```php
static::make()->myFunc(); // Instead of $this->myFunc()
// Or
static::invokes('myFunc');
```


## Keyboard
### Keyboard Formatter
```php
KeyFormatter::wrap([
    [$menu->key('A'), $menu->key('B'), $menu->key('C')],
    [$menu->key('D')],
], 2)
```

#### Wrap
Wrap:
```php
KeyFormatter::wrap([
    [$menu->key('A'), $menu->key('B'), $menu->key('C')],
    [$menu->key('D'), $menu->key('E'), $menu->key('F'), $menu->key('G')],
], 2)
```
Result is:
```php
[
    [$menu->key('A'), $menu->key('B')],
    [$menu->key('C')],
    [$menu->key('D'), $menu->key('E')],
    [$menu->key('F'), $menu->key('G')],
]
```
Wrap Hidden:
```php
KeyFormatter::wrapHidden([
    [$menu->key('A'), $menu->key('B'), $menu->key('C')],
    [$menu->key('D'), $menu->key('E'), $menu->key('F'), $menu->key('G')],
], 2)
```
Result is:
```php
[
    [$menu->key('A'), $menu->key('B')],
    [$menu->key('D'), $menu->key('E')],
]
```

#### Resize
Resize:
```php
KeyFormatter::resize([
    [$menu->key('A'), $menu->key('B'), $menu->key('C')],
    [$menu->key('D'), $menu->key('E'), $menu->key('F'), $menu->key('G')],
], 2)
```
Result is:
```php
[
    [$menu->key('A'), $menu->key('B')],
    [$menu->key('C'), $menu->key('D')],
    [$menu->key('E'), $menu->key('F')],
    [$menu->key('G')],
]
```
Or:
```php
KeyFormatter::resize(function () use ($menu) {
    for ($i = 1; $i < 6; $i++)
    {
        yield [$menu->key($i)]; // or yield $menu->key($i);
    }
}, 3)
```
Result is:
```php
[
    [$menu->key(1), $menu->key(2), $menu->key(3)],
    [$menu->key(4), $menu->key(5)],
]
```
Resize Auto:

This method resize the key using their string length.
```php
KeyFormatter::resizeAuto([
    [
        $menu->key('Hello'),
        $menu->key('How are you baby?'),
        $menu->key('OK'),
        $menu->key('Good bye'),
        $menu->key('I\'m fine'),
        $menu->key('Let\'s go'),
        $menu->key('Hello my friend im in the bathroom'),
    ]
])
```
Result is:
```php
[
    [
        $menu->key('Hello'),
        $menu->key('How are you baby?'),
    ],
    [
        $menu->key('OK'),
        $menu->key('Good bye'),
        $menu->key('I\'m fine'),
        $menu->key('Let\'s go'),
    ],
    [
        $menu->key('Hello my friend im in the bathroom'),
    ],
]
```

#### Rtl
```php
KeyFormatter::rtl([
    [$menu->key('A'), $menu->key('B')],
    [$menu->key('C'), $menu->key('D'), $menu->key('E')],
])
```
Result is:
```php
[
    [$menu->key('B'), $menu->key('A')],
    [$menu->key('E'), $menu->key('D'), $menu->key('C')],
]
```


#### Builder
```php
$result = KeyFormatter::for([...])->resize(2)->rtl()->toArray();
```


## Text

### Modes
```php
$mode = Text::mode('none');
$mode = Text::mode('html');
$mode = Text::mode('markdown'); // Recommended to use 'markdown2'
$mode = Text::mode('markdown2');
```

```php
// Normal styles:
$mode->bold('Hello world'); // <b>Hello world</b>
$mode->italic('Text'); // <i>Text</i>

// Tag user:
$mode->user($user->name, $user->id); // <a href='tg://user?id=12345678'>Ahmad</a>

// Merge many styled text (StringContent):
$mode->build($mode->bold('A'), $mode->italic('B')); // <b>A</b><i>B</i>
$mode->build('Hello ', $mode->bold('Ahmad'), $mode->italic(' =D')); // Hello<b>Ahmad</b><i> =D</i>

// Deep:
$mode->bold($mode->italic('Styled')); // <b><i>Styled</i></b>
```

### Text Builder
```php
TextBuilder::make('html', prefix: '$ ', suffix: ' $', separate: ' : ', division: "---", subtitle: "The End")
    ->line('This is simple text')
    ->space1()
    ->line('Your name', $user->name)
    ->space()
    ->div()
    ->space()
    ->blank('Checkout your profile tomorrow')
    ->toString();
```
Result is:
```text
$ This is simple text $

$ Your name : Ahmad $


---


Checkout your profile tomorrow
The End
```

Using `build` helper method:
```php
TextBuilder::make('html', prefix: '# ', separate: ' : ')
    ->build(fn (TextBuilder $text, TextModeEncoding $mode) => $text
         ->blank($mode->bold("Your Profile:"))
         ->space()
         ->line('Name', $user->name)
         ->line('Age', $user->age)
         ->line('Break the', 'rules', prefix: '~ ', suffix: ' ~', separate: '  ')
    );
```

Result is:
```text
<b>Your Profile:</b>


# Name : Ahmad
# Age : 12
~ Break the  rules ~
```











## nn
