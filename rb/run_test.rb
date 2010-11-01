require 'rubygems'
require 'haml'

test = File.read('test.haml');
engine = Haml::Engine.new(test);
puts engine.precompiled