<?php
/**
 * Base class for simple HTML_QuickForm2 containers
 *
 * PHP version 5
 *
 * LICENSE:
 * 
 * Copyright (c) 2006, Alexey Borzov <avb@php.net>, 
 *                     Bertrand Mansion <golgote@mamasam.com> 
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *    * Redistributions of source code must retain the above copyright
 *      notice, this list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above copyright
 *      notice, this list of conditions and the following disclaimer in the 
 *      documentation and/or other materials provided with the distribution.
 *    * The names of the authors may not be used to endorse or promote products 
 *      derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS
 * IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY
 * OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @license    http://opensource.org/licenses/bsd-license.php New BSD License
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */

/**
 * Base class for all HTML_QuickForm2 elements 
 */
require_once 'HTML/QuickForm2/AbstractElement.php';

/**
 * Abstract base class for simple QuickForm2 containers
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @version    Release: @package_version@
 */
abstract class HTML_QuickForm2_Container
    extends HTML_QuickForm2_AbstractElement
    implements IteratorAggregate, Countable
{
   /**
    * Array of elements contained in this container
    * @var array
    */
    protected $elements = array();

   /**
    * 'name' and 'id' attributes should be always present and their setting 
    * should go through setName() and setId(). 
    * @var array
    */
    protected $watchedAttributes = array('id', 'name');

    protected function onAttributeChange($name, $value = null)
    {
        if ('name' == $name) {
            if (null === $value) {
                throw new HTML_QuickForm2_InvalidArgumentException(
                    "Required attribute 'name' can not be removed"
                );
            } else {
                $this->setName($value);
            }
        } elseif ('id' == $name) {
            if (null === $value) {
                throw new HTML_QuickForm2_InvalidArgumentException(
                    "Required attribute 'id' can not be removed"
                );
            } else {
                $this->setId($value);
            }
        }
    }

    public function getName()
    {
        return $this->attributes['name'];
    }

    public function setName($name)
    {
        $this->attributes['name'] = (string)$name;
    }

    public function getId()
    {
        return isset($this->attributes['id'])? $this->attributes['id']: null;
    }

    public function setId($id = null)
    {
        if (is_null($id)) {
            $id = self::generateId($this->getName());
        } else {
            self::storeId($id);
        }
        $this->attributes['id'] = (string)$id;
    }

    public function toggleFrozen($freeze = null)
    {
        if (null !== $freeze) {
            foreach ($this as $child) {
                $child->toggleFrozen($freeze);
            }
        }
        return parent::toggleFrozen($freeze);
    }

    public function persistentFreeze($persistent = null)
    {
        if (null !== $persistent) {
            foreach ($this as $child) {
                $child->persistentFreeze($persistent);
            }
        }
        return parent::persistentFreeze($persistent);
    }




   /**
    * Returns an array of this container's elements
    * 
    * @return   array   Container elements
    */
    public function getElements()
    {
        return $this->elements;
    }

   /**
    * Appends an element to the container
    *
    * If the element was previously added to the container or to another
    * container, it is first removed there.
    * 
    * @param    HTML_QuickForm2_AbstractElement     Element to add
    * @return   HTML_QuickForm2_AbstractElement     Added element
    * @throws   HTML_QuickForm2_InvalidArgumentException
    */
    public function addElement(HTML_QuickForm2_AbstractElement $element)
    {
        if ($this === $element->getContainer()) {
            $this->removeChild($element);
        }
        $element->setContainer($this);
        $this->elements[] = $element;
        return $element;
    }

   /**
    * Removes the element from this container
    * 
    * If the reference object is not given, the element will be appended.
    * 
    * @param    HTML_QuickForm2_AbstractElement     Element to remove
    * @return   HTML_QuickForm2_AbstractElement     Removed object
    */
    public function removeChild(HTML_QuickForm2_AbstractElement $element)
    {

        if ($element->getContainer() !== $this) {
            throw new HTML_QuickForm2_NotFoundException(
                "Element with name '".$element->getName()."' was not found"
            );
        }
        foreach ($this as $key => $child){
            if ($child === $element) {
                unset($this->elements[$key]);
                $element->setContainer(null);
                break;
            }
        }
        return $element;
    }


   /**
    * Returns an element if its id is found
    * 
    * @param    string  Element id to find
    * @return   HTML_QuickForm2_AbstractElement|null
    */
    public function getElementById($id)
    {
        foreach ($this->getRecursiveIterator() as $element) {
            if ($id == $element->getId()) {
                return $element;
            }
        }
        return null;
    }

   /**
    * Returns an array of elements which name corresponds to element
    * 
    * @param    string  Elements name to find
    * @return   array
    */
    public function getElementsByName($name)
    {
        $found = array();
        foreach ($this->getRecursiveIterator() as $element) {
            if ($element->getName() == $name) {
                $found[] = $element;
            }
        }
        return $found;
    }

   /**
    * Inserts an element in the container
    * 
    * If the reference object is not given, the element will be appended.
    * 
    * @param    HTML_QuickForm2_AbstractElement     Element to insert
    * @param    HTML_QuickForm2_AbstractElement     Reference to insert before
    * @return   HTML_QuickForm2_AbstractElement     Inserted element
    */
    public function insertBefore(HTML_QuickForm2_AbstractElement $element, HTML_QuickForm2_AbstractElement $reference = null)
    {
        if (null === $reference) {
            return $this->addElement($element);
        }
        $offset = 0;
        foreach ($this as $child) {
            if ($child === $reference) {
                if ($this === $element->getContainer()) {
                    $this->removeChild($element);
                }
                $element->setContainer($this);
                array_splice($this->elements, $offset, 0, array($element));
                return $element;
            }
            $offset++;
        }
        throw new HTML_QuickForm2_NotFoundException(
            "Reference element with name '".$reference->getName()."' was not found"
        );
    }

   /**
    * Returns a recursive iterator for the container elements
    * 
    * @return    HTML_QuickForm2_ContainerIterator
    */
    public function getIterator()
    {
        return new HTML_QuickForm2_ContainerIterator($this);
    }

   /**
    * Returns a recursive iterator iterator for the container elements
    * 
    * @return    RecursiveIteratorIterator
    */
    public function getRecursiveIterator()
    {
        return new RecursiveIteratorIterator(
                        new HTML_QuickForm2_ContainerIterator($this),
                        RecursiveIteratorIterator::SELF_FIRST
                    );
    }

   /**
    * Returns the number of elements in the container
    * 
    * @return    int
    */
    public function count()
    {
        return count($this->elements);
    }

}

/**
 * Implements a recursive iterator for the container elements
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @version    Release: @package_version@
 */
class HTML_QuickForm2_ContainerIterator extends RecursiveArrayIterator implements RecursiveIterator
{
    public function __construct(HTML_QuickForm2_Container $container)
    {
        parent::__construct($container->getElements());
    }

    public function hasChildren()
    {
        return $this->current() instanceof HTML_QuickForm2_Container;
    }
    
    public function getChildren()
    {
        return new HTML_QuickForm2_ContainerIterator($this->current());
    }
}

?>