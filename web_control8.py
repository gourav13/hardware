from bottle import route, run
import RPi.GPIO as GPIO

host = '192.168.0.103'

GPIO.setmode(GPIO.BCM)
led_pins = [18, 23, 24]
led_states = [0, 0, 0]
switch_pin = 25

GPIO.setup(led_pins[0], GPIO.OUT)
GPIO.setup(led_pins[1], GPIO.OUT)
GPIO.setup(led_pins[2], GPIO.OUT)
GPIO.setup(switch_pin, GPIO.IN, pull_up_down=GPIO.PUD_UP)
def ledstate1(led):
    p=led_states[led]
    l=str(p)
    return l

def switch_status():
    state = GPIO.input(switch_pin)
    if state:
        return 'Up'
    else:
        return 'Down'

def html_for_led(led):
    l = str(led)
    result = " <input type='button' onClick='changed("+l+",3)' value='on " + l + "'/><input type='button' onClick='changed("+l+",4)' value='off " + l + "'/>"
    return result

def update_leds():
    for i, value in enumerate(led_states):
        GPIO.output(led_pins[i], value)

@route('/')
@route('/<led>/<q>')

def index(led="n",q="n"):
    if led != "n":
        led_num = int(led)
        p=int(q)
        if(p==3):
           led_states[led_num] =  1
        update_leds()
        if(p==4):
           led_states[led_num] =  0   
        update_leds()
    response = "<script> "
    response += "function changed(led,q)"
    response += "{"
    response += " window.location.href='/' + led+'/'+q"
    response += "}"
    response += "</script>"
    response += '<h1>GPIO Control</h1>'
    response += '<h2>Button=' + switch_status() + '</h2>'
    response += '<h2>LEDs</h2>'
    response += '<body>'
    response +='<p> led 0'+ledstate1(0)+'</p>'
    response += html_for_led(0)
    response +='<p> led 1 '+ledstate1(1)+'</p>'
    response += html_for_led(1)
    response +='<p> led 2'+ledstate1(2)+'</p>'
    response += html_for_led(2)
    response += '</body>'
    return response

run(host='192.168.0.103', port=80)
