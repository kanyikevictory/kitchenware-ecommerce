import { useEffect, useRef, useState } from 'react'
import { Outlet, useLocation } from 'react-router-dom'
import { useReveal } from '../hooks/useReveal'
import CartDrawer from './CartDrawer'
import Footer from './Footer'
import Header from './Header'
import ToastStack from './ToastStack'

function Cursor() {
  const ref = useRef(null), target = useRef({x:-20,y:-20}), current = useRef({x:-20,y:-20})
  useEffect(() => { if (matchMedia('(pointer: coarse)').matches) return; let frame; const move = (e) => target.current={x:e.clientX,y:e.clientY}; const tick=()=>{current.current.x+=(target.current.x-current.current.x)*.18; current.current.y+=(target.current.y-current.current.y)*.18; if(ref.current) ref.current.style.transform=`translate3d(${current.current.x-6}px,${current.current.y-6}px,0)`; frame=requestAnimationFrame(tick)}; addEventListener('mousemove',move); tick(); return()=>{removeEventListener('mousemove',move);cancelAnimationFrame(frame)} },[])
  return <div ref={ref} className="custom-cursor fixed left-0 top-0 z-[9999] hidden h-3 w-3 pointer-events-none rounded-full bg-terracotta md:block" />
}

export default function Layout() {
  const [loading,setLoading]=useState(()=>!sessionStorage.getItem('mf-loaded')), [progress,setProgress]=useState(0); const location=useLocation(); useReveal()
  useEffect(()=>{if(!loading)return; const timer=setTimeout(()=>{sessionStorage.setItem('mf-loaded','1');setLoading(false)},1900);return()=>clearTimeout(timer)},[loading])
  useEffect(()=>{scrollTo(0,0)},[location.pathname])
  useEffect(()=>{const fn=()=>{const max=document.documentElement.scrollHeight-innerHeight;setProgress(max>0?(scrollY/max)*100:0)};addEventListener('scroll',fn,{passive:true});fn();return()=>removeEventListener('scroll',fn)},[])
  useReveal()
  return <div className="min-h-screen bg-cream"><div className="fixed left-0 top-0 z-[9998] h-[3px] bg-terracotta" style={{width:`${progress}%`}} />{loading&&<div className="fixed inset-0 z-[9997] grid place-items-center bg-cream transition"><div className="text-center"><div className="font-serif text-4xl text-navy">Maison & Flame</div><div className="mx-auto mt-5 h-px w-48 overflow-hidden bg-sand"><div className="h-full bg-terracotta animate-progress"/></div></div></div>}<Cursor/><Header/><main><Outlet/></main><Footer/><CartDrawer/><ToastStack/></div>
}
