<?php


//THIS IS A SMALL PIECE OF A BIG ECOMMERCE PLATFORM THAT DEMONSTRATES CLARITY AND EASE OF THE DEVELOPMENT AND CODING

//method_shipping() AND view_shipping()
//THESE METHODS DEFINE FUNCTIONALITY AND VIEW OF THE THE SHIPPING PAGE


//method_payment()
//THIS METHOD DEMONSTRATES PRELOADING DATA FOR A DYNAMIC PAGE (ONE OF MANY NEAT MECHANISMS IMPLEMENTED IN THE PLATFORM)
//IDEA IS TO MAKE THE DATA AVAILABLE IN ADVANCE FOR THE AJAX MECHANISM, WHICH WOULD OTHERWISE FETCH IT ON DOCUMENT READY
//SOMETIMES, WE WANT THAT APPROACH FOR THE SERVER EFFICIENCY, SOMETIMES (FOR THE PERFORMANCE REASONS) WE WANT TO POSTPONE IT

class Store extends Component{

    public function __construct($instance_id=null,$label=null){
        parent::__construct(CONTROL,__CLASS__,$instance_id,$label);
    }
    
    public function connect_component(){
        include(LITE_GLOBALS);
        
        //DIRECT DEPENDENCIES
        $this->pairs['system_user']=&$system_user;
    }   
    
    
    /*
    
        .
        .
        .
        THOUSANDS OF LINES OF CODES (NOT INCLUDED FOR THE PRIVACY REASONS)
        .
        .
        .
    
    
    */
    

    public function method_payment(){
        include(LITE_GLOBALS);

        //VIEW INDEX
        $this->attrs['view_identifier'] ='payment';

        //NAVIGATION LINKS FOR CURRENT STATE
        $this->vars['shipping_link']    =$system_url_controller->page_name_to_url('Shipping');
        $this->vars['place_order_link'] =$system_url_controller->page_name_to_url('Place Order');
    

        //WE WANT TO PRELOAD AVAILABLE PAYMENT METHODS (TURN OFF THIS ATTACHMENT FOR LAZY LOAD)
        $ajax=[
                'title'             => 'get_payments_methods',  
                'component_name'    => 'store',
                'component_method'  => 'default',
                'ajax_method'       => 'get_payments_methods',
                'ajax_arguments'    => [],
                
              ];
        
        
        $system_document_manager->attach_ajax($ajax);
    
    
    
        //WE WANT TO PRELOAD TEMPLATE PACKAGE (TURN OFF THIS ATTACHMENT FOR LAZY LOAD)
        $system_document_manager->attach_template('payments_methods_package','store/template_payments_methods_package');
    
    }
    
    public function method_shipping(){
        include(SYSTEM_GLOBALS);
        
        //GENERATING BACK LINK (IN CASE USER WANTS TO EDIT PROFILE BEFORE SUBMITTING SHIPPING DATA)
        if(isset($system_url_controller->current['back_link'])){
            $this->vars['back_link']=$system_url_controller->link_to_url($system_url_controller->current['back_link']);
        }else{
            //WE WANT TO GENERATE LINK FOR THE USER TO BE ABLE TO JUMP BACK AFTER FINISH EDITING HIS PROFILE 
            $this->vars['back_link']=$system_url_controller->page_location_url_with_jump('Edit User');
        }
        
        //NAVIGATION LINKS FOR CURRENT STATE
        $this->vars['cart_link']    =$system_url_controller->page_name_to_url('Cart');
        $this->vars['payment_link'] =$system_url_controller->page_name_to_url('Payment');
    
    
        //VIEW INDEX
        $this->attrs['view_identifier']='shipping';


        //WE DONT USE INITIAL DATA, BUT WE WANT TO PASS THESE AS REFERENCES AFTER SUCCESSFUL SUBMITION
        $data           =null;
        $address_data   =null;

        
        $form=new Form('store_shipping',[
            
            //INIT DATA (SOME STATIC DATA NEEDED FOR THE PROCCESS... ETC.)
            'data'      => &$data,
    
            //LANG INDEX, IF FORM USES LANGUAGE OF THE CONTROL (OR EVEN SOME OTHER COMPONENT), OTHERWISE IS DEFAULT
            'lang'      => $this->attrs['language_index'],
            
            
            //THIS METHOD ANALYZES SUBMITED DATA, AND RETURNS TRUE/FALSE. IF FALSE, THEN 
            'analyze'   => function($form){
                include(LITE_GLOBALS);
                
                //ANALIZE FUNCTION USES FORM WRAPPER FUNCTION AND RETURNS TRUE OR FALSE, OR STATUS/VALUE RESPONSE
                return $system_ecommerce->set_buyer_data_form($form);
            },
            
            'error'     => function($form){
                //WE LEAVE EMPTY FOR DEFAULT BEHAVIOR
                $result=['status'=>'error', 'message'=>'M_SHIPPING_METHOD_PROCCESS_ERROR'];
                return $result;
            }, 
            
            //IF ANALYZE METHOD RETURNS SUCCESS WE PERFORM THIS METHOD, IT IS PROCCESSED BEFORE REDIRECTION
            'success'   => function($form){
                include(LITE_GLOBALS);
                
                //WE USE DEFAULT BEHAVIOR PLUS PUSHING SOME TRIGGERS FOR ANALYTICS
                $analytics_data=['content_name'=>  'Shipping Address'];
                $system_analytics->push_event('Lead', $analytics_data);
            }, 
            
            
            //THIS METHOD IS USED TO FILL FORM WITH DATA. IT DOES NOT EXECUTE IN THE PROCESS OF SUBMITIG/REDIRECTION, BUT BEFORE OR AFTER
            //AND ITS STATE IS DEFINED BY THE STATE OF THE USER'S DATA (WHICH COULD BE DEFAULT, PARCIAL, OR COMPLETE: EG. HE IS LOGGED IN, OR HIS ADDRESS IS SUCCESSFULLY UPDATED, AND DATA IS SERVED FROM THE DB)
            'fill'      => function($form) use (&$address_data){
                include(LITE_GLOBALS);
            
                //FILL FUNCTION MUST PROVIDE TWO INFOS: ARRAY 'fill_data' WHICH CONTAINS KEYS FOR EVERY FIELD, AND 'is_default'
                return $address_data=$system_ecommerce->get_buyer_addresses_form($form);
            }, 
    
    
            //VIEW FOR THE GENERATED CONTENT
            'view'      => &$this->vars['form_content'],
            
        ]);

    
        //WE USE ADDRESS DATA TO GENERATE LIST INTO THE FRONTEND
        $this->vars['addresses_list']=$this->generate_addresses_list($address_data);


        //WE WANT TO CREATE SIMULTANCE THREAD FOR CHECKING IF PHONE NUMBER IS VALID AND REGISTERED FOR THE GLOBAL SERVICING
        $simultance_data    =['mobile_phone' => $address_data['mobile_phone']]; 
        $system_simultance  ->create('verify_phone',$simultance_data);

        
    }
    
        
    /*
    
        .
        .
        .
        THOUSANDS OF LINES OF CODES (NOT INCLUDED FOR THE PRIVACY REASONS)
        .
        .
        .
    
    
    */
    
    
    public function view_payment(){
        include(SYSTEM_GLOBALS);
        
        //TEMPLATE INDEX
        $this->attrs['template_index']='_payment';
        
        //WE WANT TO LOAD THESE LABELS FOR THE CURRENT LANGUAGE
        $this->vars['labels_substitutions']=['{L_PAYMENT}','{L_SHIPPING}','{L_ORDER}'];

        //PAGE CONTENT VIEW 
        $this->vars['positions_substitutions']=[
            ['{P_SHIPPING_LINK}'        , &$this->vars['shipping_link']         ],                  
            ['{P_PLACE_ORDER_LINK}'     , &$this->vars['place_order_link']      ],
        ]; 

    }

    
    
    public function view_shipping(){
        include(SYSTEM_GLOBALS);
        
        //TEMPLATE INDEX
        $this->attrs['template_index']='_shipping';
        
        //WE WANT TO LOAD THESE LABELS FOR THE CURRENT LANGUAGE
        $this->vars['labels_substitutions']=['{L_SHIPPING}','{L_EMAIL}','{L_FIRSTNAME}','{L_LASTNAME}','{L_COMPANY}','{L_ADDRESS}','{L_BACK}','{L_SAVE}'];

        //PAGE CONTENT VIEW 
        $this->vars['positions_substitutions']=[
            ['{P_FORM}'                 , &$this->vars['form_content']          ],
            ['{P_POST_LINK}'            , &$this->vars['self_url']              ],
            ['{P_BACK_LINK}'            , &$this->vars['back_link']             ],          
            ['{P_ADDRESSES_LIST}'       , &$this->vars['addresses_list']        ],
            ['{P_CART_LINK}'            , &$this->vars['cart_link']             ],
            ['{P_PAYMENT_LINK}'         , &$this->vars['payment_link']          ],
        ]; 

    }
    
    
    
}


    