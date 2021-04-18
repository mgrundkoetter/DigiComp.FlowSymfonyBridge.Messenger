<?php

namespace DigiComp\FlowSymfonyBridge\Messenger\Transport;

use Doctrine\DBAL\Driver\AbstractPostgreSQLDriver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Bridge\Doctrine\Transport\Connection;
use Symfony\Component\Messenger\Bridge\Doctrine\Transport\DoctrineTransport;
use Symfony\Component\Messenger\Bridge\Doctrine\Transport\PostgreSqlConnection;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class FlowDoctrineTransportFactory implements TransportFactoryInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function createTransport(string $dsn, array $options, SerializerInterface $serializer): TransportInterface
    {
        $useNotify = ($options['use_notify'] ?? true);
        unset($options['transport_name'], $options['use_notify']);
        // Always allow PostgreSQL-specific keys, to be able to transparently fallback to the native driver
        // when LISTEN/NOTIFY isn't available
        $configuration = PostgreSqlConnection::buildConfiguration($dsn, $options);

        try {
            $driverConnection = $this->entityManager->getConnection();
        } catch (\InvalidArgumentException $e) {
            throw new TransportException(sprintf(
                'Could not find Doctrine connection from Messenger DSN "%s".',
                $dsn
            ), 0, $e);
        }

        if ($useNotify && $driverConnection->getDriver() instanceof AbstractPostgreSQLDriver) {
            $connection = new PostgreSqlConnection($configuration, $driverConnection);
        } else {
            $connection = new Connection($configuration, $driverConnection);
        }

        return new DoctrineTransport($connection, $serializer);
    }

    /**
     * @inheritDoc
     */
    public function supports(string $dsn, array $options): bool
    {
        return 0 === strpos($dsn, 'flow-doctrine://');
    }
}
