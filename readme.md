# Laravel Data Transfer Requests

Do you like strong types, full code completion and proper static analysis without having to repeat yourself? 
Introducing Laravel Data Transfer Requests!

Adds seamless and concise data oriented request objects to Laravel as a plug-in replacement to the usual FormRequest objects. 
You will be able to define your request Data Transfer Objects and validation rules in the same class, without having to repeat yourself.

Simply type-hint your DataTransferRequest class instead of a FromRequest.

## Installation

``composer require niki9796dk/laravel-data-transfer-requests``

## Usage
To use the package, simply create a class which extends the DataTransferRequest class, and define your request fields as public fields with their required type.

### Data Transfer Requests

```php
// StorePostData.php
class StorePostData extends DataTransferRequest
{
    public string $title;
    public ?string $subTitle = 'Default Value';
    public string $content;
    public ?Carbon $releaseDate;
}

// PostController.php
class PostController extends Controller
{
    public function store(StorePostData $post)
    {
        return $this->createNewPost($post);
    }
    
    // Somewhere else
    private function createNewPost(StorePostData $post): Post
    {
        return Post::create([
            'title'       => $post->title,
            'subTitle'    => $post->subTitle,
            'content'     => $post->content,
            'releaseData' => $post->releaseDate,
        ]);
    }
}
```

### Equivalent

```php
// StorePostRequest.php
class StorePostRequest extends FormRequest
{
    public function rules()
    {
        return [
            'title'       => ['required', 'string'],
            'subTitle'    => ['nullable', 'string'],
            'content'     => ['required', 'string'],
            'releaseData' => ['nullable', 'date'],
        ];
    }
    
    public function toDto(): StorePostDto
    {
        return new StorePostDto(
            $this->input('title'),
            $this->input('subTitle', 'Default Value'),
            $this->input('content'),
            $this->input('releaseData'),
        )
    }
}

// StorePostDto.php
class StorePostDto
{
    public string $title;
    public ?string $subTitle;
    public string $content;
    public ?Carbon $releaseDate;
}

// PostController.php
class PostController extends Controller
{
    public function store(StorePostRequest $post)
    {
        return $this->createNewPost($post->toDto());
    }
    
    // Somewhere else
    private function createNewPost(StorePostDto $post): Post
    {
        return Post::create([
            'title'       => $post->title,
            'subTitle'    => $post->subTitle,
            'content'     => $post->content,
            'releaseData' => $post->releaseDate,
        ]);
    }
}
```
