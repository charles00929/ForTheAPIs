<?php
namespace BWTV\ForTheAPIs\Tests\Utilities;

use BWTV\ForTheAPIs\Controllers\APIBaseController;
use Illuminate\Routing\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;

trait RuntimeSetup{

    /**
     * Create an anonymous controller,
     * it has 'newMethod' function to mock business logic.
     * @return object
     */
    protected function createController(){
        $testInstance = $this;
        return new class($testInstance) extends Controller
        {
            private $methods = [];
            private $testInstance;

            public function __construct($testInstance)
            {
                $this->testInstance = $testInstance;
            }

            public function __call($name, $args = [])
            {
                if (isset($this->methods[$name])) {
                    return call_user_func_array($this->methods[$name], $args);
                }

                return NULL;
            }

            public function newMethod($name, \Closure $method)
            {
                $this->methods[$name] = $method->bindTo($this->testInstance, $this->testInstance);
            }
        };
    }

    /**
     * Create a mock model with given data
     * @param array $data
     * @return Model
     */
    protected function createMockModel(array $data = [])
    {
        return new class($data) extends Model {
            protected $fillable = ['*'];
            protected $guarded = [];
            
            public function __construct(array $attributes = [])
            {
                parent::__construct();
                $this->setRawAttributes($attributes);
                $this->exists = true; // 標記為已存在的記錄
            }
            
            public function toArray()
            {
                return $this->attributesToArray();
            }
        };
    }

    /**
     * Create a mock resource with given data
     * @param mixed $data
     * @return JsonResource
     */
    protected function createMockResource($data = [])
    {
        return new class($data) extends JsonResource {
            public function toArray($request = null)
            {
                if (is_array($this->resource)) {
                    return $this->resource;
                }
                
                if (method_exists($this->resource, 'toArray')) {
                    return $this->resource->toArray();
                }
                
                return (array) $this->resource;
            }
        };
    }
}