require 'spec_helper'

describe file('/etc/php5/fpm/conf.d/php-custom.ini') do
  it { should be_file }
  it { should contain(/short_open_tag = Off/) }
  it { should contain(/allow_call_time_pass_reference = Off/) }
  it { should contain(/max_execution_time = 30/) }
  it { should contain(/memory_limit = 128M/) }
  it { should contain(/error_reporting = E_ALL | E_STRICT/) }
  it { should contain(/display_errors = On/) }
  it { should contain(/log_errors = On/) }
  it { should contain(/log_errors_max_len = 1024/) }
  it { should contain(/ignore_repeated_errors = Off/) }
  it { should contain(/ignore_repeated_source = Off/) }
  it { should contain(/track_errors = Off/) }
  it { should contain(/html_errors = 1/) }
  it { should contain(/error_log = \/srv\/log\/php_errors.log/) }
  it { should contain(/post_max_size = 50M/) }
  it { should contain(/upload_max_filesize = 50M/) }
  it { should contain(/max_file_uploads = 20/) }
  it { should contain(/default_socket_timeout = 60/) }
end
