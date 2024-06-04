<?php
require 'vendor/autoload.php';
use Carbon\Carbon;
class WarehouseItem
{
    private int $id;
    private string $name;
    private int $quantity;
    private Carbon $lastModified;
    private Carbon $dateCreated;
    public function __construct(int $quantity, string $name)
    {
        $this->name = $name;
        $this->quantity = $quantity;
        $this->lastModified = Carbon::now('UTC');
        $this->dateCreated = Carbon::now('UTC');
        $this->setId();
    }
    private function setId(): void
    {
        $items = $this->loadItems();
        if (empty($items)) {
            $this->id = 1;
        } else {
            $lastItem = end($items);
            $this->id = $lastItem['id'] + 1;
        }
    }
    public static function loadItems(): array
    {
        if (!file_exists('warehouse_items.json')) {
            return [];
        }
        $data = file_get_contents('warehouse_items.json');
        $items = json_decode($data, true);
        return $items;
    }
    public function save(): void
    {
        $items = self::loadItems();
        $updated = false;
        foreach ($items as &$item) {
            if ($item['id'] == $this->id) {
                $item = $this->toArray();
                $updated = true;
                break;
            }
        }
        if (!$updated) {
            $items[] = $this->toArray();
        }
        file_put_contents('warehouse_items.json', json_encode($items, JSON_PRETTY_PRINT));
    }
    public function getId(): int
    {
        return $this->id;
    }
    public function getName(): string
    {
        return $this->name;
    }
    public function getQuantity(): int
    {
        return $this->quantity;
    }
    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
        $this->lastModified = Carbon::now('UTC');
    }
    public function getLastModified(): Carbon
    {
        return $this->lastModified;
    }
    public function getDateCreated(): Carbon
    {
        return $this->dateCreated;
    }
    public function toArray(): array
    {
        return [
              'id' => $this->id,
              'name' => $this->name,
              'quantity' => $this->quantity,
              'lastModified' => $this->lastModified->toDateTimeString(),
              'dateCreated' => $this->dateCreated->toDateTimeString(),
        ];
    }
    public static function fromArray(array $data): self
    {
        $item = new self($data['quantity'], $data['name']);
        $item->id = $data['id'];
        if (isset($data['lastModified'])) {
            $item->lastModified = Carbon::now('UTC');
        }
        if (isset($data['dateCreated'])) {
            $item->dateCreated =Carbon::now('UTC');
        }
        return $item;
    }
}

        if (isset($data['dateCreated'])) {
            $item->dateCreated =Carbon::now('UTC');
        }
        return $item;
    }
}



