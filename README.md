# DigiComp.FlowSymfonyBridge.Messenger

![Build status](https://ci.digital-competence.de/api/badges/Packages/DigiComp.FlowSymfonyBridge.Messenger/status.svg)

This packages brings a DI configuration for the `symfony/messenger` component, so it can be used easily in `neos/flow` projects.

To see how to use it, you probably want to have a look at the [documentation](https://symfony.com/doc/current/messenger.html) of `symfony/messenger`.


## Getting started

To get it integrated, you all need to do is to get message bus injected:

```php 
    #[Flow\Inject]
    protected MessageBusInterface $messageBus;
```

And later in your method:

```php
    $this->messageBus->dispatch(new CustomMessage())
```

You should configure a routing, to let the messenger know, over which transport your message should be handled:

```yaml
DigiComp:
  FlowSymfonyBridge:
    Messenger:
      transports:
        "custom-messages":
          dsn: "flow-doctrine://default?table_name=test_messenger_messages"
      routing:
        Acme\Vendor\Messenger\CustomMessage:
          - "custom-messages"
```

In this example we are using a doctrine transport (the speciality "flow-transport" is a transport which uses the already existing connection to doctrine instead of creating a new one - for the rest of the DSN-Format have a look in the documentation of `symfony/messenger`)

A handler for your CustomMessage could look like this:

```php
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CustomMessageHandler
{
    public function __invoke(CustomMessage $message)
    {
        //your code here
    }
}
```

It will be automatically found by Flow // the messenger and messages arriving at the bus will be handled by your handler.

Probably you'll want to consume the messengers with long living processes or as a cronjob. The Flow command for that task is `messenger:consume` (more help available)
