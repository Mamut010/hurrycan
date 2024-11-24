<?php
namespace App\Http\Responses;

use App\Http\Dtos\IllustrationDto;
use App\Http\Dtos\ShopUserDto;

class ProductResponse
{
    public int $id;
    public string $name;
    public string $originalPrice;
    public string $price;
    public ?string $briefDescription;
    public ?string $detailDescription;
    public int $shopId;
    public ?string $averageRating;
    public string $discount;
    public ?\DateTimeImmutable $createdAt;
    public ?\DateTimeImmutable $updatedAt;

    public ShopUserDto $shop;

    /**
     * @var IllustrationDto[]
     */
    public array $illustrations;
}
