import{C as c,c as f,o as w,w as n,a as o,b as t,u as e,m as _,d as V,n as g,e as b}from"./app-COn3urlr.js";import{_ as k}from"./GuestLayout-D9ivfFJf.js";import{_ as l,a as i,b as m}from"./TextInput-DKt-GxrQ.js";import{P as v}from"./PrimaryButton-Bc5Bb6bX.js";import"./Navbar-4Zk6FVPN.js";import"./_plugin-vue_export-helper-DlAUqK2U.js";const y={class:"mt-4"},P={class:"mt-4"},q={class:"mt-4 flex items-center justify-end"},U={__name:"ResetPassword",props:{email:{type:String,required:!0},token:{type:String,required:!0}},setup(u){const d=u,s=c({token:d.token,email:d.email,password:"",password_confirmation:""}),p=()=>{s.post(route("password.store"),{onFinish:()=>s.reset("password","password_confirmation")})};return(x,a)=>(w(),f(k,null,{default:n(()=>[o(e(_),{title:"Reset Password"}),t("form",{onSubmit:V(p,["prevent"])},[t("div",null,[o(l,{for:"email",value:"Email"}),o(i,{id:"email",type:"email",class:"mt-1 block w-full",modelValue:e(s).email,"onUpdate:modelValue":a[0]||(a[0]=r=>e(s).email=r),required:"",autofocus:"",autocomplete:"username"},null,8,["modelValue"]),o(m,{class:"mt-2",message:e(s).errors.email},null,8,["message"])]),t("div",y,[o(l,{for:"password",value:"Password"}),o(i,{id:"password",type:"password",class:"mt-1 block w-full",modelValue:e(s).password,"onUpdate:modelValue":a[1]||(a[1]=r=>e(s).password=r),required:"",autocomplete:"new-password"},null,8,["modelValue"]),o(m,{class:"mt-2",message:e(s).errors.password},null,8,["message"])]),t("div",P,[o(l,{for:"password_confirmation",value:"Confirm Password"}),o(i,{id:"password_confirmation",type:"password",class:"mt-1 block w-full",modelValue:e(s).password_confirmation,"onUpdate:modelValue":a[2]||(a[2]=r=>e(s).password_confirmation=r),required:"",autocomplete:"new-password"},null,8,["modelValue"]),o(m,{class:"mt-2",message:e(s).errors.password_confirmation},null,8,["message"])]),t("div",q,[o(v,{class:g({"opacity-25":e(s).processing}),disabled:e(s).processing},{default:n(()=>a[3]||(a[3]=[b(" Reset Password ")])),_:1},8,["class","disabled"])])],32)]),_:1}))}};export{U as default};
