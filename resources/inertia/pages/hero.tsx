import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import style from "./style.module.css";
import { gsap } from "gsap";
import { useGSAP } from "@gsap/react";
import { Button } from "../components/cn/button"
import { useRef } from "react";


export default function Home() {
  const headingRef = useRef<HTMLHeadingElement>(null);
  const descriptionRef = useRef<HTMLHeadingElement>(null);

  useGSAP(() => {
    if (headingRef.current && descriptionRef.current) {
      const letters = headingRef.current.querySelectorAll('.letter');
      const description = descriptionRef.current;
      
      // Create a timeline for sequencing animations
      const tl = gsap.timeline();
      
      // Set initial states
      gsap.set(letters, { 
        opacity: 0, 
        y: 50,
        rotationX: 90,
        transformOrigin: "50% 50% -50px"
      });
      
      gsap.set(description, {
        opacity: 0,
        y: -20
      });
      
      // Add heading animation to timeline
      tl.to(letters, {
        opacity: 1,
        y: 0,
        rotationX: 0,
        duration: 1.2,
        stagger: 0.05,
        ease: "power2.out",
        delay: 0.3
      })
      // Add description animation after heading completes
      .to(description, {
        opacity: 1,
        y: 0,
        duration: 1.5,
        ease: "power2.out"
      }, "-=0.8"); // Start slightly before heading fully completes for smooth transition
    }
  }, { scope: headingRef });

  return (
    <div className={style.wrapper}>
      <nav className={style.topnav}>
        <div className={style.topnavLeft}>
          <h1 className="font-bold text-xl">ClosureBox</h1>
        </div>
        
        <div className={style.topnavCenter +" space-x-2"} >
          <a href="#product-section"><Button variant="ghost">Products</Button></a>
          <a href=""><Button variant="ghost">Pricing</Button></a>
          <a href=""><Button variant="ghost">Contact</Button></a>
        </div>

        <div className={style.topnavRight + " space-x-2"}>
          <a href="/register">
            <Button variant="default">Register</Button>
          </a>
          <a href="/login">
            <Button>Login</Button>
          </a>
        </div>
      </nav>

      <main className={style.contentContainer}>
        <section className={style.heroContainer}>
          <div className={style.heroCard}>
            <h1 ref={headingRef} className={style.callOutText}>
              {"Fearless Cloud Architecting.".split(' ').map((letter, index) => (
                <span key={index} className="letter inline-block">
                  {letter === ' ' ? ' ' : letter}
                  <span className="ml-6"></span>
                </span>
                
              ))}
            </h1>
            <div ref={descriptionRef} className="flex flex-col text-center m-auto ">
                <p className="text-2xl" style={{
                  opacity:"0.9",
                  marginTop:"40px"
                }}>A serverless cloud platform service for Australia & New Zealand.</p>
                
                <span className="mb-6"></span>
                <div className="flex flex-wrap flex-1 gap-2 text-center m-auto">
                  <a href="">
                    <Button>Get Started</Button>
                  </a>
                  <a href="">
                    <Button variant="ghost">Pricing</Button>
                  </a>
                </div>
            </div>
              
            </div>

        </section>


        <section className="" id="product-section">


        </section>
      </main>
    </div>
  );
}
